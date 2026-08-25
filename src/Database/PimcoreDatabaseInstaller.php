<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use Doctrine\DBAL\Connection;
use Pimcore\Bundle\InstallBundle\Database\DatabaseSetup;

/**
 * Installs a fresh Pimcore database (core schema, optional seed data, and an admin user) on a
 * given {@see Connection}.
 *
 * A booted Pimcore/Symfony kernel is required beforehand (i.e. `\Pimcore::getContainer()` must be
 * initialized), because {@see DatabaseSetup::createOrUpdateAdminUser()} reads the password
 * hashing configuration from it. This does not have to be the current request's kernel though -
 * calling `\Pimcore\Bootstrap::startupCli()` first is enough, so this can also be used standalone
 * from a project's own database setup script, e.g. to fetch the `Connection` from the container
 * (`$container->get('doctrine.dbal.default_connection')`) without a dedicated console command.
 */
final class PimcoreDatabaseInstaller
{
    /**
     * @param array{username: string, password: string}|null $adminCredentials defaults to the username `admin` with a
     *                                                                         random password
     */
    public function install(Connection $db, bool $insertSeedData, ?array $adminCredentials = null): void
    {
        $databaseSetup = new DatabaseSetup();
        $databaseSetup->createSchema($db);

        if ($insertSeedData) {
            $databaseSetup->insertSeedData($db);
        }

        $databaseSetup->createOrUpdateAdminUser($db, $adminCredentials ?? [
            'username' => 'admin',
            'password' => bin2hex(random_bytes(16)),
        ]);
    }
}
