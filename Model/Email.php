<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use JetBrains\PhpStorm\ArrayShape;
use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Util\StringUtil;

final class Email implements ValueObject
{
    private string $email;

    private ?string $name;

    /**
     * @return static
     */
    public static function fromString(string $email, string $name = null): self
    {
        return new self($email, $name);
    }
    public static function fromArray(array $data): self
    {
        return new self($data['email'], $data['name'] ?? null);
    }

    private function __construct(string $email, string $name = null)
    {
        $email = StringUtil::trim($email);
        $name = StringUtil::trim($name);

        Assert::notEmpty($email);
        if (!(new EmailValidator())->isValid($email, new NoRFCWarningsValidation())) {
            throw new \InvalidArgumentException(sprintf('The email "%s" is invalid.', $email));
        }

        if (mb_strlen((string) $name) > 100) {
            $name = mb_substr($name, 0, 97).'…';
        }

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

    #[ArrayShape(['email' => 'string', 'name' => 'null|string'])]
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'name'  => $this->name,
        ];
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
     * Obfuscates the email, but only picking the first 2 characters, the first after the @ and the TLD.
     */
    public function obfuscated(): string
    {
        return sprintf(
            '%s…@%s….%s',
            substr($this->email, 0, 2),
            substr($this->email, strpos($this->email, '@') + 1, 1),
            substr($this->email, strrpos($this->email, '.') + 1),
        );
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (self::class !== $other::class) {
            return false;
        }

        return strtolower($this->email) === strtolower($other->email);
    }
}
