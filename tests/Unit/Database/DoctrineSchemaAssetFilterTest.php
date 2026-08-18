<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Database;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\JoinTableMapping;
use Doctrine\ORM\Mapping\ManyToManyOwningSideMapping;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Neusta\Pimcore\TestingFramework\Internal\Database\DoctrineSchemaAssetFilter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class DoctrineSchemaAssetFilterTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    #[Test]
    public function it_runs_the_callback_unfiltered_for_a_non_entity_manager(): void
    {
        $manager = $this->prophesize(ObjectManager::class);

        $registry = $this->prophesize(ManagerRegistry::class);
        $registry->getManager('foo')->willReturn($manager->reveal());

        $filter = new DoctrineSchemaAssetFilter($registry->reveal());

        $called = false;
        $filter->runForManager('foo', static function () use (&$called): void {
            $called = true;
        });

        self::assertTrue($called);
    }

    /** @test */
    #[Test]
    public function it_scopes_the_schema_assets_filter_to_managed_tables_via_orm3_mapping_objects(): void
    {
        if (!class_exists(ManyToManyOwningSideMapping::class)) {
            self::markTestSkipped('Requires doctrine/orm 3.x, which maps associations via typed objects instead of arrays.');
        }

        $mapping = new ManyToManyOwningSideMapping('related', \stdClass::class, \stdClass::class);
        $mapping->joinTable = new JoinTableMapping('owner_related');

        $metadata = new ClassMetadata(\stdClass::class);
        $metadata->setPrimaryTable(['name' => 'owner_table']);
        $metadata->associationMappings = ['related' => $mapping];

        $this->assertFilterScopesToManagedTables([$metadata], ['owner_table', 'owner_related']);
    }

    /** @test */
    #[Test]
    public function it_scopes_the_schema_assets_filter_to_managed_tables_via_orm2_array_fallback(): void
    {
        $metadata = new ClassMetadata(\stdClass::class);
        $metadata->setPrimaryTable(['name' => 'owner_table']);
        $metadata->associationMappings = [
            'related' => [
                'joinTable' => ['name' => 'owner_related'],
            ],
        ];

        $this->assertFilterScopesToManagedTables([$metadata], ['owner_table', 'owner_related']);
    }

    /** @test */
    #[Test]
    public function it_combines_with_the_previous_filter(): void
    {
        $metadata = new ClassMetadata(\stdClass::class);
        $metadata->setPrimaryTable(['name' => 'owner_table']);

        $configuration = new Configuration();
        // The previous filter already rejects "owner_table" - the scoped filter must still respect that,
        // even though "owner_table" is a managed table.
        $configuration->setSchemaAssetsFilter(static fn (string $assetName): bool => 'owner_table' !== $assetName);

        $filter = $this->buildFilter($configuration, [$metadata]);

        $filter->runForManager('foo', static function () use ($configuration): void {
            $scopedFilter = $configuration->getSchemaAssetsFilter();

            self::assertFalse($scopedFilter('owner_table'));
        });
    }

    /** @test */
    #[Test]
    public function it_restores_the_previous_filter_even_if_the_callback_throws(): void
    {
        $previousFilter = static fn (): bool => true;
        $configuration = new Configuration();
        $configuration->setSchemaAssetsFilter($previousFilter);

        $filter = $this->buildFilter($configuration, []);

        try {
            $filter->runForManager('foo', static function (): void {
                throw new \RuntimeException('boom');
            });
            self::fail('Expected exception was not thrown.');
        } catch (\RuntimeException) {
            // expected
        }

        self::assertSame($previousFilter, $configuration->getSchemaAssetsFilter());
    }

    /**
     * @param list<ClassMetadata<*>> $metadata
     * @param list<string> $managedTables
     */
    private function assertFilterScopesToManagedTables(array $metadata, array $managedTables): void
    {
        $configuration = new Configuration();
        $filter = $this->buildFilter($configuration, $metadata);

        $filter->runForManager('foo', static function () use ($configuration, $managedTables): void {
            $scopedFilter = $configuration->getSchemaAssetsFilter();

            foreach ($managedTables as $table) {
                self::assertTrue($scopedFilter($table), \sprintf('Expected "%s" to be managed.', $table));
            }

            self::assertFalse($scopedFilter('some_other_managers_table'));
        });
    }

    /**
     * @param list<ClassMetadata<*>> $metadata
     */
    private function buildFilter(Configuration $configuration, array $metadata): DoctrineSchemaAssetFilter
    {
        $connection = $this->prophesize(Connection::class);
        $connection->getConfiguration()->willReturn($configuration);

        $metadataFactory = $this->prophesize(ClassMetadataFactory::class);
        $metadataFactory->getAllMetadata()->willReturn($metadata);

        $manager = $this->prophesize(EntityManagerInterface::class);
        $manager->getConnection()->willReturn($connection->reveal());
        $manager->getMetadataFactory()->willReturn($metadataFactory->reveal());

        $registry = $this->prophesize(ManagerRegistry::class);
        $registry->getManager('foo')->willReturn($manager->reveal());

        return new DoctrineSchemaAssetFilter($registry->reveal());
    }
}
