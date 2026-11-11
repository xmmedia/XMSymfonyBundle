<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Sentry;

use PHPUnit\Framework\Attributes\DataProvider;
use Sentry\Breadcrumb;
use Sentry\Event;
use Sentry\ExceptionDataBag;
use Xm\SymfonyBundle\Infrastructure\Sentry\TokenScrubber;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class TokenScrubberTest extends BaseTestCase
{
    /** @return list<array{0: string}> */
    public static function tokenPathProvider(): array
    {
        return [
            ['/activate/'],
            ['/verify/'],
            ['/recover/reset/'],
        ];
    }

    #[DataProvider('tokenPathProvider')]
    public function testScrubsThePath(string $path): void
    {
        $this->assertSame(
            'https://example.com'.$path.'[Filtered]',
            $this->scrubber()->scrub('https://example.com'.$path.$this->token()),
        );
    }

    public function testKeepsWhatFollowsThePath(): void
    {
        $this->assertSame(
            'GET /activate/[Filtered]?a=1',
            $this->scrubber()->scrub('GET /activate/'.$this->token().'?a=1'),
        );
        $this->assertSame(
            'Bad "/activate/[Filtered]".',
            $this->scrubber()->scrub('Bad "/activate/'.$this->token().'".'),
        );
    }

    public function testLeavesPathsWithoutATokenAlone(): void
    {
        foreach (['/activate', '/verify', '/recover/reset', '/recover/reset/', '/recover/initiate'] as $path) {
            $this->assertSame($path, $this->scrubber()->scrub($path));
        }
    }

    public function testUsesTheReplacement(): void
    {
        $scrubber = new TokenScrubber(['activate' => [
            'prefix'      => '/activate/',
            'segment'     => null,
            'replacement' => '[$token\1]',
        ]]);

        $this->assertSame('/activate/[$token\1]', $scrubber->scrub('/activate/'.$this->token()));
    }

    public function testOnlyScrubsASegmentThatMatches(): void
    {
        $token = $this->faker()->regexify('[A-Za-z0-9_-]{43}');
        $scrubber = new TokenScrubber(['client' => [
            'prefix'      => '/client/',
            'segment'     => '[A-Za-z0-9_-]{43}',
            'replacement' => '[token]',
        ]]);

        $this->assertSame('/client/[token]/invoice', $scrubber->scrub('/client/'.$token.'/invoice'));

        $uuid = '/client/'.$this->faker()->uuid();
        $this->assertSame($uuid, $scrubber->scrub($uuid));

        // one character too long isn't the whole segment
        $this->assertSame('/client/'.$token.'x', $scrubber->scrub('/client/'.$token.'x'));
    }

    public function testScrubsTheQueryParam(): void
    {
        $query = \sprintf('user=%s&expires=%d&hash=', $this->faker()->email(), $this->faker()->unixTime());

        $this->assertSame(
            'https://example.com/login-link?'.$query.'[Filtered]',
            $this->scrubber()->scrub('https://example.com/login-link?'.$query.$this->hash()),
        );
        $this->assertSame(
            '/login-link?hash=[Filtered]&expires=1',
            $this->scrubber()->scrub('/login-link?hash='.$this->hash().'&expires=1'),
        );
    }

    public function testLeavesTheQueryParamOnOtherPathsAlone(): void
    {
        $value = 'https://example.com/admin?hash='.$this->hash();

        $this->assertSame($value, $this->scrubber()->scrub($value));
    }

    public function testLeavesOtherQueryParamsAlone(): void
    {
        $value = '/login-link?hashtag=a&rehash=b';

        $this->assertSame($value, $this->scrubber()->scrub($value));
    }

    public function testScrubsTheRequestsQueryStringOnThePath(): void
    {
        $event = Event::createEvent();
        $event->setRequest([
            'url'          => 'https://example.com/login-link',
            'query_string' => 'user=a&expires=1&hash='.$this->hash(),
        ]);

        $this->assertSame(
            'user=a&expires=1&hash=[Filtered]',
            $this->scrubber()($event)->getRequest()['query_string'],
        );
    }

    public function testLeavesOtherRequestsQueryStringAlone(): void
    {
        $queryString = 'hash='.$this->hash();

        foreach (['https://example.com/admin', 'https://example.com/login-link-other'] as $url) {
            $event = Event::createEvent();
            $event->setRequest(['url' => $url, 'query_string' => $queryString]);

            $this->assertSame($queryString, $this->scrubber()($event)->getRequest()['query_string']);
        }
    }

    public function testScrubsWithAPattern(): void
    {
        $scrubber = new TokenScrubber(patterns: ['api_key' => [
            'regex'       => '~(api_key: )\w+~',
            'replacement' => '$1[Filtered]',
        ]]);

        $this->assertSame('api_key: [Filtered]', $scrubber->scrub('api_key: '.$this->token()));
    }

    public function testDoesNothingWithoutConfig(): void
    {
        $value = '/activate/'.$this->token();

        $this->assertSame($value, new TokenScrubber()->scrub($value));
    }

    public function testScrubsTheEvent(): void
    {
        $path = '/activate/'.$this->token();

        $event = Event::createEvent();
        $event->setRequest(['url' => 'https://example.com'.$path, 'query_string' => 'a=1']);
        $event->setTransaction('GET '.$path);
        $event->setMessage('Failed on '.$path);
        $event->setExtra(['path' => $path, 'nested' => ['url' => $path]]);
        $event->setTags(['url' => $path]);
        $event->setContext('request', ['uri' => $path]);
        $event->setBreadcrumb([
            new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_HTTP,
                'http',
                'GET '.$path,
                ['url' => $path, 'status' => 302],
            ),
        ]);
        $exception = new ExceptionDataBag(new \RuntimeException());
        $exception->setValue('Bad '.$path);

        $event->setExceptions([$exception]);

        $scrubbed = $this->scrubber()($event);

        $this->assertSame(
            ['url' => 'https://example.com/activate/[Filtered]', 'query_string' => 'a=1'],
            $scrubbed->getRequest(),
        );
        $this->assertSame('GET /activate/[Filtered]', $scrubbed->getTransaction());
        $this->assertSame('Failed on /activate/[Filtered]', $scrubbed->getMessage());
        $this->assertSame(
            ['path' => '/activate/[Filtered]', 'nested' => ['url' => '/activate/[Filtered]']],
            $scrubbed->getExtra(),
        );
        $this->assertSame(['url' => '/activate/[Filtered]'], $scrubbed->getTags());
        $this->assertSame(['uri' => '/activate/[Filtered]'], $scrubbed->getContexts()['request']);

        $breadcrumb = $scrubbed->getBreadcrumbs()[0];
        $this->assertSame('GET /activate/[Filtered]', $breadcrumb->getMessage());
        $this->assertSame(['url' => '/activate/[Filtered]', 'status' => 302], $breadcrumb->getMetadata());

        $this->assertSame('Bad /activate/[Filtered]', $scrubbed->getExceptions()[0]->getValue());
    }

    private function scrubber(): TokenScrubber
    {
        return new TokenScrubber(
            [
                'activate' => ['prefix' => '/activate/', 'segment' => null, 'replacement' => '[Filtered]'],
                'verify'   => ['prefix' => '/verify/', 'segment' => null, 'replacement' => '[Filtered]'],
                'reset'    => ['prefix' => '/recover/reset/', 'segment' => null, 'replacement' => '[Filtered]'],
            ],
            ['login_link' => ['path' => '/login-link', 'param' => 'hash', 'replacement' => '[Filtered]']],
        );
    }

    private function token(): string
    {
        return $this->faker()->regexify('[A-Za-z0-9]{40}');
    }

    private function hash(): string
    {
        return $this->faker()->regexify('[A-Za-z0-9_~-]{43}');
    }
}
