<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Sentry;

use Sentry\Breadcrumb;
use Sentry\Event;

/**
 * Replaces tokens in URLs in what's sent to Sentry (its before_send): the request, extra, tags, contexts, transaction,
 * message, breadcrumbs & exception values. Sentry only filters values by their name, so a token in a URL path, or in a
 * query param whose name doesn't look sensitive, would otherwise be kept.
 *
 * Configured in xm_symfony.sentry_scrubber: see the bundle's AGENTS.md.
 */
final readonly class TokenScrubber
{
    // the characters that end a URL's path segment or query param value, including where it's quoted in a message
    private const string END = '/?#&\s"\'<>';

    /** @var array<string, string> pattern => replacement, applied to every string */
    private array $patterns;

    /** @var list<array{path: string, pattern: string, replacement: string}> for the request's query string */
    private array $queryStringPatterns;

    /**
     * @param array<string, array{prefix: string, segment: ?string, replacement: string}> $paths
     * @param array<string, array{path: string, param: string, replacement: string}>      $queryParams
     * @param array<string, array{regex: string, replacement: string}>                    $patterns
     */
    public function __construct(array $paths = [], array $queryParams = [], array $patterns = [])
    {
        $compiled = [];
        $queryStringPatterns = [];

        foreach ($paths as $path) {
            $segment = $path['segment'] ?? '[^'.self::END.']+';
            $pattern = \sprintf('~(%s)(?:%s)(?![^%s])~', preg_quote($path['prefix'], '~'), $segment, self::END);
            $compiled[$pattern] = '$1'.$this->escapeReplacement($path['replacement']);
        }

        foreach ($queryParams as $queryParam) {
            $param = preg_quote($queryParam['param'], '~');
            $replacement = '$1'.$this->escapeReplacement($queryParam['replacement']);

            $pattern = \sprintf(
                '~(%s\?(?:[^#\s"\'<>]*&)?%s=)[^%s]+~',
                preg_quote($queryParam['path'], '~'),
                $param,
                self::END,
            );
            $compiled[$pattern] = $replacement;

            $queryStringPatterns[] = [
                'path'        => \sprintf('~%s(?:$|[?#])~', preg_quote($queryParam['path'], '~')),
                'pattern'     => \sprintf('~((?:^|&)%s=)[^&]+~', $param),
                'replacement' => $replacement,
            ];
        }

        foreach ($patterns as $pattern) {
            $compiled[$pattern['regex']] = $pattern['replacement'];
        }

        $this->patterns = $compiled;
        $this->queryStringPatterns = $queryStringPatterns;
    }

    public function __invoke(Event $event): Event
    {
        $event->setRequest($this->scrubRequest($event->getRequest()));
        $event->setExtra($this->scrubArray($event->getExtra()));
        $event->setTags($this->scrubArray($event->getTags()));
        foreach ($event->getContexts() as $name => $context) {
            $event->setContext($name, $this->scrubArray($context));
        }

        $transaction = $event->getTransaction();
        if (null !== $transaction) {
            $event->setTransaction($this->scrub($transaction));
        }

        $message = $event->getMessage();
        if (null !== $message) {
            $event->setMessage($this->scrub($message));
        }

        $event->setBreadcrumb(array_map($this->scrubBreadcrumb(...), $event->getBreadcrumbs()));

        foreach ($event->getExceptions() as $exception) {
            $exception->setValue($this->scrub($exception->getValue()));
        }

        return $event;
    }

    public function scrub(string $value): string
    {
        if ([] === $this->patterns) {
            return $value;
        }

        return preg_replace(array_keys($this->patterns), array_values($this->patterns), $value) ?? $value;
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return array<array-key, mixed>
     */
    public function scrubArray(array $data): array
    {
        return array_map(
            fn (mixed $value): mixed => match (true) {
                \is_string($value) => $this->scrub($value),
                \is_array($value)  => $this->scrubArray($value),
                default            => $value,
            },
            $data,
        );
    }

    /**
     * Sentry sends the query string on its own, without the path, so a query param is only replaced there when the
     * request's URL is the param's path.
     *
     * @param array<array-key, mixed> $request
     *
     * @return array<array-key, mixed>
     */
    private function scrubRequest(array $request): array
    {
        $url = $request['url'] ?? null;
        $queryString = $request['query_string'] ?? null;

        if (\is_string($url) && \is_string($queryString)) {
            foreach ($this->queryStringPatterns as $queryStringPattern) {
                if (1 === preg_match($queryStringPattern['path'], $url)) {
                    $queryString = preg_replace(
                        $queryStringPattern['pattern'],
                        $queryStringPattern['replacement'],
                        $queryString,
                    ) ?? $queryString;
                }
            }
            $request['query_string'] = $queryString;
        }

        return $this->scrubArray($request);
    }

    private function scrubBreadcrumb(Breadcrumb $breadcrumb): Breadcrumb
    {
        $message = $breadcrumb->getMessage();

        return new Breadcrumb(
            $breadcrumb->getLevel(),
            $breadcrumb->getType(),
            $breadcrumb->getCategory(),
            null === $message ? null : $this->scrub($message),
            $this->scrubArray($breadcrumb->getMetadata()),
            $breadcrumb->getTimestamp(),
        );
    }

    private function escapeReplacement(string $replacement): string
    {
        return addcslashes($replacement, '\\$');
    }
}
