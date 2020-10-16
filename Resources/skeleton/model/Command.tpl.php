<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;
use <?= $name_class; ?>;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class <?= $class_name; ?> extends Command
{
    public static function now(
        <?= $id_class_short; ?> $<?= $id_property; ?>,
        Name $name
    ): self {
        return new self([
            '<?= $id_property; ?>' => $<?= $id_property; ?>->toString(),
            'name' => $name->toString(),
        ]);
    }

    public function <?= $id_property; ?>(): <?= $id_class_short; ?><?= "\n"; ?>
    {
        return <?= $id_class_short; ?>::fromString($this->payload['<?= $id_property; ?>']);
    }

    public function name(): Name
    {
        return Name::fromString($this->payload['name']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, '<?= $id_property; ?>');
        Assert::uuid($payload['<?= $id_property; ?>']);

        Assert::keyExists($payload, 'name');
        Assert::string($payload['name']);

        parent::setPayload($payload);
    }
}
