<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * @internal
 */
final class PimcoreDatabaseResetter
{
    private ManagerRegistry $registry;
    private RunCommand $runCommand;

    public function __construct(Application $application, ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->runCommand = new RunCommand($application);
    }

    public function resetDatabase(): void
    {
        $this->dropAndCreateDatabase();
        $this->createSchema();
    }

    public function resetSchema(): void
    {
        $this->dropSchema();
        $this->createSchema();
    }

    private function dropAndCreateDatabase(): void
    {
        ($this->runCommand)(
            'doctrine:database:drop',
            [
                '--connection' => $this->registry->getDefaultConnectionName(),
                '--if-exists' => true,
                '--force' => true,
            ]
        );

        ($this->runCommand)(
            'doctrine:database:create',
            [
                '--connection' => $this->registry->getDefaultConnectionName(),
            ]
        );
    }

    private function dropSchema(): void
    {
        if (self::isResetUsingDump()) {
            $this->dropAndCreateDatabase();

            return;
        }

        if ($manager = $this->registry->getDefaultManagerName()) {
            ($this->runCommand)(
                'doctrine:schema:drop',
                [
                    '--em' => $manager,
                    '--force' => true,
                ]
            );
        }
    }

    private function createSchema(): void
    {
        $installer = new PimcoreInstaller();

        if (self::isResetUsingDump()) {
            $installer->setDumpLocation($_SERVER['DATABASE_DUMP_LOCATION']);
        }

        if ([] !== $errors = $installer->setupDatabase([])) {
            throw new \RuntimeException(sprintf(
                'Error setting up Pimcore\'s database: "%s"',
                implode('", "', $errors),
            ));
        }

        ($this->runCommand)(
            'pimcore:deployment:classes-rebuild',
            [
                '--create-classes' => true,
            ]
        );

        if (!self::isResetUsingDump() && $manager = $this->registry->getDefaultManagerName()) {
            ($this->runCommand)(
                'doctrine:schema:update',
                [
                    '--em' => $manager,
                    '--force' => true,
                ]
            );
        }
    }

    private static function isResetUsingDump(): bool
    {
        return '' !== ($_SERVER['DATABASE_DUMP_LOCATION'] ?? '');
    }
}
