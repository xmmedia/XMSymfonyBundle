<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Type\UuidTypeTrait;

class <?= $class_name; ?> extends ScalarType implements AliasedInterface
{
    use UuidTypeTrait;

    private const string NAME = '<?= $id_class_short; ?>';

    public function __construct()
    {
        parent::__construct([
            'name'        => self::NAME,
            'description' => 'A UUID v4 for a <?= $model; ?> represented as string.',
        ]);
    }

    /**
     * @param string|mixed $value
     */
    public function parseValue($value): ?<?= $id_class_short; ?><?= "\n"; ?>
    {
        if (\is_string($value) && Uuid::isValid($value)) {
            return <?= $id_class_short; ?>::fromString($value);
        }

        throw new Error('Cannot represent value as UUID: '.Utils::printSafe($value));
    }

    public static function getAliases(): array
    {
        return [self::NAME];
    }
}
