<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\AssociationMapping;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ManyToManyOwningSideMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @internal
 */
final class DoctrineSchemaAssetFilter
{
    public function __construct(
        private readonly ManagerRegistry $registry,
    ) {
    }

    /**
     * @param callable(): void $callback
     */
    public function runForManager(string $managerName, callable $callback): void
    {
        $manager = $this->registry->getManager($managerName);

        if (!$manager instanceof EntityManagerInterface) {
            $callback();

            return;
        }

        $configuration = $manager->getConnection()->getConfiguration();
        $previousFilter = $configuration->getSchemaAssetsFilter();
        $managedTables = $this->loadManagedTables($manager);

        $configuration->setSchemaAssetsFilter(
            static function (AbstractAsset|string $assetName) use ($previousFilter, $managedTables): bool {
                if (null !== $previousFilter && !$previousFilter($assetName)) {
                    return false;
                }

                if ($assetName instanceof AbstractAsset) {
                    $assetName = $assetName->getName();
                }

                $assetName = trim($assetName, '`"');

                return \in_array($assetName, $managedTables, true);
            },
        );

        try {
            $callback();
        } finally {
            $configuration->setSchemaAssetsFilter($previousFilter);
        }
    }

    /**
     * @return list<string>
     */
    private function loadManagedTables(EntityManagerInterface $manager): array
    {
        $managedTables = [];

        foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
            $this->addManagedTable($managedTables, $metadata->getTableName());
            $this->addManagedJoinTables($managedTables, $metadata);
        }

        return $managedTables;
    }

    /**
     * @param list<string> $managedTables
     */
    private function addManagedTable(array &$managedTables, string $tableName): void
    {
        if (\in_array($tableName, $managedTables, true)) {
            return;
        }

        $managedTables[] = $tableName;
    }

    /**
     * @param ClassMetadata<*> $metadata
     * @param list<string> $managedTables
     */
    private function addManagedJoinTables(array &$managedTables, ClassMetadata $metadata): void
    {
        /** @var AssociationMapping|array<mixed> $associationMapping Doctrine ORM 2 returns array mappings, ORM 3 returns AssociationMapping objects. */
        foreach ($metadata->getAssociationMappings() as $associationMapping) {
            if ($associationMapping instanceof ManyToManyOwningSideMapping) {
                $this->addManagedTable($managedTables, $associationMapping->joinTable->name);

                continue;
            }

            // Fallback for Doctrine ORM 2
            if (
                \is_array($associationMapping)
                && isset($associationMapping['joinTable']['name'])
                && \is_string($associationMapping['joinTable']['name'])
            ) {
                $this->addManagedTable($managedTables, $associationMapping['joinTable']['name']);
            }
        }
    }
}
