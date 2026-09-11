<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * What's shown while the site's in maintenance mode & who can still use it. Stored as JSON in
 * the maintenance file (see MaintenanceMode).
 */
final readonly class MaintenanceSettings implements \JsonSerializable
{
    public const string DEFAULT_MESSAGE = 'We\'ll be back shortly.';
    // sent when there's no end time: long enough that crawlers don't hammer the site
    private const int DEFAULT_RETRY_AFTER = 300;

    /**
     * @param list<string> $allowedIps IPs or CIDR ranges that can use the site
     */
    public function __construct(
        private ?string $message = null,
        private ?\DateTimeImmutable $until = null,
        private array $allowedIps = [],
    ) {
    }

    /**
     * Anything unreadable falls back to the defaults, so an empty file (a bare `touch`) or one
     * edited by hand still turns maintenance mode on.
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (!\is_array($data)) {
            return new self();
        }

        $message = null;
        if (\is_string($data['message'] ?? null) && '' !== trim($data['message'])) {
            $message = trim($data['message']);
        }

        $until = null;
        if (\is_string($data['until'] ?? null)) {
            try {
                $until = CarbonImmutable::parse($data['until']);
            } catch (\Exception) {
            }
        }

        $allowedIps = [];
        if (\is_array($data['allowedIps'] ?? null)) {
            $allowedIps = array_values(array_filter($data['allowedIps'], is_string(...)));
        }

        return new self($message, $until, $allowedIps);
    }

    public function message(): ?string
    {
        return $this->message;
    }

    public function displayMessage(): string
    {
        return $this->message ?? self::DEFAULT_MESSAGE;
    }

    public function until(): ?\DateTimeImmutable
    {
        return $this->until;
    }

    /**
     * @return list<string>
     */
    public function allowedIps(): array
    {
        return $this->allowedIps;
    }

    public function withMessage(?string $message): self
    {
        if (null !== $message && '' === trim($message)) {
            $message = null;
        }

        return new self(null === $message ? null : trim($message), $this->until, $this->allowedIps);
    }

    public function withUntil(?\DateTimeImmutable $until): self
    {
        return new self($this->message, $until, $this->allowedIps);
    }

    /**
     * @param list<string> $allowedIps
     */
    public function withAllowedIps(array $allowedIps): self
    {
        return new self($this->message, $this->until, array_values(array_unique($allowedIps)));
    }

    public function allows(?string $ip): bool
    {
        if (null === $ip || [] === $this->allowedIps) {
            return false;
        }

        return IpUtils::checkIp($ip, $this->allowedIps);
    }

    /**
     * Seconds until the end time, for the Retry-After header.
     */
    public function retryAfter(\DateTimeImmutable $now): int
    {
        if (null === $this->until || $this->until <= $now) {
            return self::DEFAULT_RETRY_AFTER;
        }

        return $this->until->getTimestamp() - $now->getTimestamp();
    }

    public function jsonSerialize(): array
    {
        return [
            'message'    => $this->message,
            'until'      => $this->until?->format(\DATE_ATOM),
            'allowedIps' => $this->allowedIps,
        ];
    }
}
