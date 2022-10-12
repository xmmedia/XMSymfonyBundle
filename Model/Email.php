<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Util\StringUtil;

final class Email implements ValueObject
{
    /** @var string */
    private $email;

    /** @var string|null */
    private $name;

    /**
     * @return static
     */
    public static function fromString(string $email, ?string $name = null): self
    {
        return new static($email, $name);
    }

    private function __construct(string $email, ?string $name = null)
    {
        $email = StringUtil::trim($email);
        $name = StringUtil::trim($name);

        Assert::notEmpty($email);
        Assert::true(
            (new EmailValidator())->isValid($email, new NoRFCWarningsValidation()),
            sprintf('The email "%s" is invalid.', $email)
        );

        Assert::nullOrMaxLength($name, 50, 'The name must be less than 50 characters.');

        $this->email = $email;
        $this->name = $name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function toString(): string
    {
        return $this->email;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function withName(): string
    {
        if (null === $this->name) {
            return $this->email;
        }

        // replace commas and semicolons with space because email services will assume it's multiple email addresses
        $name = StringUtil::trim(substr(str_replace([',', ';'], ' ', $this->name), 0, 20));

        return sprintf('%s <%s>', $name, $this->email);
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        return strtolower($this->email) === strtolower($other->email);
    }
}
