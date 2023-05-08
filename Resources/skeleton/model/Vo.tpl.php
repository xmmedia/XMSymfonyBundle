<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\Util\Assert;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\StringUtil;

final readonly class <?= $class_name; ?> implements ValueObject
{
    public const MIN_LENGTH = 2;
    public const MAX_LENGTH = 50;

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    private function __construct(private string $name)
    {
        $name = StringUtil::trim($name);

        Assert::notEmpty($name);
        Assert::lengthBetween($name, self::MIN_LENGTH, self::MAX_LENGTH);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toString(): string
    {
        return $this->name();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function sameValueAs(self|ValueObject $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        return $this->name === $other->name;
    }
}
