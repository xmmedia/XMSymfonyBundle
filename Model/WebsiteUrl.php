<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Xm\SymfonyBundle\Util\Assert;
use Xm\SymfonyBundle\Util\StringUtil;

class WebsiteUrl implements ValueObject
{
    public const MAX_LENGTH = 2000;

    private readonly string $url;

    public static function fromString(string $url): static
    {
        return new static($url);
    }

    private function __construct(string $url)
    {
        $url = StringUtil::trim($url);

        Assert::notEmpty($url);
        Assert::url($url, ['https', 'http']);
        Assert::maxLength($url, static::MAX_LENGTH);

        $this->url = $url;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function toString(): string
    {
        return $this->url();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param static|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== $other::class) {
            return false;
        }

        return $this->url === $other->url;
    }
}
