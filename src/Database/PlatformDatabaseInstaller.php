<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use Doctrine\DBAL\Connection;
use Pimcore\Bundle\InstallBundle\Database\DatabaseSetup;

/**
 * @internal
 */
final class PlatformDatabaseInstaller
{
    public function install(Connection $db, bool $insertSeedData): void
    {
        $databaseSetup = new DatabaseSetup();
        $databaseSetup->createSchema($db);

        if ($insertSeedData) {
            $databaseSetup->insertSeedData($db);
        }

        $databaseSetup->createOrUpdateAdminUser($db, [
            'username' => 'admin',
            'password' => bin2hex(random_bytes(16)),
        ]);
    }
}
