<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Doctrine;

use Xm\SymfonyBundle\Doctrine\FilterQueryBuilder;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Util\FiltersInterface;

class FilterQueryBuilderTest extends BaseTestCase
{
    private function createConcreteFilterQueryBuilder(): FilterQueryBuilder
    {
        return new class extends FilterQueryBuilder {
            public function queryParts(FiltersInterface $filters): array
            {
                if ($filters->applied('name')) {
                    $this->whereClauses[] = 'name = :name';
                    $this->parameters['name'] = $filters->get('name');
                }

                if ($filters->applied('q')) {
                    $this->applyBasicQ($filters, 'q', ['name', 'email']);
                }

                if ($filters->applied('status')) {
                    $this->whereClauses[] = 'status = :status';
                    $this->parameters['status'] = $filters->get('status');
                }

                if ($filters->applied('orderBy')) {
                    $this->order = 'ORDER BY '.$filters->get('orderBy');
                }

                return [
                    'joins'          => implode(' ', $this->joins),
                    'where'          => 'WHERE '.implode(' AND ', $this->whereClauses),
                    'parameters'     => $this->parameters,
                    'parameterTypes' => $this->parameterTypes,
                    'order'          => $this->order,
                ];
            }
        };
    }

    public function testQueryPartsWithNoFilters(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')->andReturn(false);

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertEquals('', $result['joins']);
        $this->assertEquals('WHERE 1', $result['where']);
        $this->assertEquals([], $result['parameters']);
        $this->assertEquals([], $result['parameterTypes']);
        $this->assertEquals('', $result['order']);
    }

    public function testQueryPartsWithSingleFilter(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')
            ->with('name')
            ->andReturn(true);
        $filters->shouldReceive('applied')
            ->andReturn(false);
        $filters->shouldReceive('get')
            ->with('name')
            ->andReturn('John Doe');

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertStringContainsString('name = :name', $result['where']);
        $this->assertEquals(['name' => 'John Doe'], $result['parameters']);
    }

    public function testApplyBasicQWithSingleTerm(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')
            ->with('q')
            ->andReturn(true);
        $filters->shouldReceive('applied')
            ->andReturn(false);
        $filters->shouldReceive('get')
            ->with('q')
            ->andReturn('john');

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertStringContainsString('name LIKE :q0', $result['where']);
        $this->assertStringContainsString('email LIKE :q0', $result['where']);
        $this->assertEquals(['q0' => '%john%'], $result['parameters']);
    }

    public function testApplyBasicQWithMultipleTermsSpaceSeparated(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')
            ->with('q')
            ->andReturn(true);
        $filters->shouldReceive('applied')
            ->andReturn(false);
        $filters->shouldReceive('get')
            ->with('q')
            ->andReturn('john doe');

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertStringContainsString('name LIKE :q0', $result['where']);
        $this->assertStringContainsString('email LIKE :q0', $result['where']);
        $this->assertStringContainsString('name LIKE :q1', $result['where']);
        $this->assertStringContainsString('email LIKE :q1', $result['where']);
        $this->assertEquals(['q0' => '%john%', 'q1' => '%doe%'], $result['parameters']);
    }

    public function testApplyBasicQWithMultipleTermsCommaSeparated(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')
            ->with('q')
            ->andReturn(true);
        $filters->shouldReceive('applied')
            ->andReturn(false);
        $filters->shouldReceive('get')
            ->with('q')
            ->andReturn('john,doe');

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertStringContainsString('name LIKE :q0', $result['where']);
        $this->assertStringContainsString('email LIKE :q0', $result['where']);
        $this->assertStringContainsString('name LIKE :q1', $result['where']);
        $this->assertStringContainsString('email LIKE :q1', $result['where']);
        $this->assertEquals(['q0' => '%john%', 'q1' => '%doe%'], $result['parameters']);
    }

    public function testApplyBasicQWithMixedSeparators(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')
            ->with('q')
            ->andReturn(true);
        $filters->shouldReceive('applied')
            ->andReturn(false);
        $filters->shouldReceive('get')
            ->with('q')
            ->andReturn('john doe,smith');

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertEquals(['q0' => '%john%', 'q1' => '%doe%', 'q2' => '%smith%'], $result['parameters']);
    }

    public function testApplyBasicQWithCommaSpaceSeparator(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')
            ->with('q')
            ->andReturn(true);
        $filters->shouldReceive('applied')
            ->andReturn(false);
        $filters->shouldReceive('get')
            ->with('q')
            ->andReturn('first, last');

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertEquals(['q0' => '%first%', 'q1' => '%last%'], $result['parameters']);
    }

    public function testQueryPartsWithMultipleFilters(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')->with('name')->andReturn(true);
        $filters->shouldReceive('applied')->with('status')->andReturn(true);
        $filters->shouldReceive('applied')->andReturn(false);
        $filters->shouldReceive('get')->with('name')->andReturn('John');
        $filters->shouldReceive('get')->with('status')->andReturn('active');

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertStringContainsString('name = :name', $result['where']);
        $this->assertStringContainsString('status = :status', $result['where']);
        $this->assertStringContainsString(' AND ', $result['where']);
        $this->assertEquals(['name' => 'John', 'status' => 'active'], $result['parameters']);
    }

    public function testQueryPartsWithOrderBy(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')->with('orderBy')->andReturn(true);
        $filters->shouldReceive('applied')->andReturn(false);
        $filters->shouldReceive('get')->with('orderBy')->andReturn('name ASC');

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertEquals('ORDER BY name ASC', $result['order']);
    }

    public function testQueryPartsWithJoins(): void
    {
        $builder = new class extends FilterQueryBuilder {
            public function queryParts(FiltersInterface $filters): array
            {
                $this->joins[] = 'JOIN user_profile up ON up.user_id = u.id';

                return [
                    'joins'          => implode(' ', $this->joins),
                    'where'          => 'WHERE '.implode(' AND ', $this->whereClauses),
                    'parameters'     => $this->parameters,
                    'parameterTypes' => $this->parameterTypes,
                    'order'          => $this->order,
                ];
            }
        };

        $filters = \Mockery::mock(FiltersInterface::class);

        $result = $builder->queryParts($filters);

        $this->assertEquals('JOIN user_profile up ON up.user_id = u.id', $result['joins']);
    }

    public function testApplyBasicQWithLeadingAndTrailingSpaces(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')->with('q')->andReturn(true);
        $filters->shouldReceive('applied')->andReturn(false);
        $filters->shouldReceive('get')->with('q')->andReturn('  john  ');

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertEquals(['q0' => '%john%'], $result['parameters']);
    }

    public function testApplyBasicQWithConsecutiveDelimiters(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')->with('q')->andReturn(true);
        $filters->shouldReceive('applied')->andReturn(false);
        $filters->shouldReceive('get')->with('q')->andReturn('john  doe');

        $builder = $this->createConcreteFilterQueryBuilder();
        $result = $builder->queryParts($filters);

        $this->assertEquals(['q0' => '%john%', 'q1' => '%doe%'], $result['parameters']);
    }

    public function testReset(): void
    {
        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')
            ->with('name')
            ->andReturn(true);
        $filters->shouldReceive('applied')
            ->andReturn(false);
        $filters->shouldReceive('get')
            ->with('name')
            ->andReturn('John');

        $builder = $this->createConcreteFilterQueryBuilder();

        $result1 = $builder->queryParts($filters);
        $this->assertEquals(['name' => 'John'], $result1['parameters']);

        $builder->reset();

        $filters2 = \Mockery::mock(FiltersInterface::class);
        $filters2->shouldReceive('applied')->andReturn(false);

        $result2 = $builder->queryParts($filters2);
        $this->assertEquals([], $result2['parameters']);
        $this->assertEquals('WHERE 1', $result2['where']);
    }

    public function testResetClearsAllProperties(): void
    {
        $builder = $this->createConcreteFilterQueryBuilder();

        $filters = \Mockery::mock(FiltersInterface::class);
        $filters->shouldReceive('applied')
            ->with('name')
            ->andReturn(true);
        $filters->shouldReceive('applied')
            ->with('status')
            ->andReturn(true);
        $filters->shouldReceive('applied')
            ->with('orderBy')
            ->andReturn(true);
        $filters->shouldReceive('applied')
            ->andReturn(false);
        $filters->shouldReceive('get')
            ->with('name')
            ->andReturn('Test');
        $filters->shouldReceive('get')
            ->with('status')
            ->andReturn('active');
        $filters->shouldReceive('get')
            ->with('orderBy')
            ->andReturn('name ASC');

        $builder->queryParts($filters);

        $result = $builder->reset();

        $this->assertSame($builder, $result);

        $emptyFilters = \Mockery::mock(FiltersInterface::class);
        $emptyFilters->shouldReceive('applied')->andReturn(false);

        $resetResult = $builder->queryParts($emptyFilters);
        $this->assertEquals([], $resetResult['parameters']);
        $this->assertEquals('', $resetResult['order']);
    }
}
