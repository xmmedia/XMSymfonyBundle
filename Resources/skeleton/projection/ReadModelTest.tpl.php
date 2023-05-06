<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $read_model_class; ?>;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;

class <?= $class_name; ?> extends BaseTestCase
{
    public function testInit(): void
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->twice()
            ->withArgs(function (string $sql) {
                return (bool) strpos($sql, '`<?= $projection_name; ?>`');
            });

        (new <?= $read_model_class_short; ?>($connection))->init();
    }

    public function testInsert(): void
    {
        $faker = $this->faker();
        $data = $types = ['key' => $faker->string(5)];

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('insert')
            ->once()
            ->withArgs(
                function (
                    string $table,
                    array $passedData,
                    array $passedTypes,
                ) use ($data, $types): bool {
                    $this->assertEquals('<?= $projection_name; ?>', $table);
                    $this->assertEquals($data, $passedData);
                    $this->assertEquals($types, $passedTypes);

                    return true;
                },
            );

        $reflection = new \ReflectionClass(<?= $read_model_class_short; ?>::class);
        $method = $reflection->getMethod('insert');
        $method->setAccessible(true);

        $method->invokeArgs(new <?= $read_model_class_short; ?>($connection), [$data, $types]);
    }

    public function testUpdate(): void
    {
        $faker = $this->faker();
        $<?= $id_property; ?> = $faker->uuid();
        $data = $types = ['key' => $faker->string(5)];

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('update')
            ->once()
            ->withArgs(
                function (
                    string $table,
                    array $passedData,
                    array $passedCriteria,
                    array $passedTypes,
                ) use ($<?= $id_property; ?>, $data, $types): bool {
                    $this->assertEquals('<?= $projection_name; ?>', $table);
                    $this->assertEquals($data, $passedData);
                    $this->assertEquals(['<?= $id_field; ?>' => $<?= $id_property; ?>], $passedCriteria);
                    $this->assertEquals($types, $passedTypes);

                    return true;
                },
            );

        $reflection = new \ReflectionClass(<?= $read_model_class_short; ?>::class);
        $method = $reflection->getMethod('update');
        $method->setAccessible(true);

        $method->invokeArgs(new <?= $read_model_class_short; ?>($connection), [$<?= $id_property; ?>, $data, $types]);
    }

    public function testRemove(): void
    {
        $faker = $this->faker();
        $<?= $id_property; ?> = $faker->uuid();

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('delete')
            ->once()
            ->withArgs(
                function (
                    string $table,
                    array $passedCriteria,
                ) use ($<?= $id_property; ?>): bool {
                    $this->assertEquals('<?= $projection_name; ?>', $table);
                    $this->assertEquals(['<?= $id_field; ?>' => $<?= $id_property; ?>], $passedCriteria);

                    return true;
                },
            );

        $reflection = new \ReflectionClass(<?= $read_model_class_short; ?>::class);
        $method = $reflection->getMethod('remove');
        $method->setAccessible(true);

        $method->invokeArgs(new <?= $read_model_class_short; ?>($connection), [$<?= $id_property; ?>]);
    }
}
