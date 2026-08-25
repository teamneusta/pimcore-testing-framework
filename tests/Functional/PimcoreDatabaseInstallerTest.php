<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Neusta\Pimcore\TestingFramework\Database\PimcoreDatabaseInstaller;
use Neusta\Pimcore\TestingFramework\Database\RunCommand;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Bundle\InstallBundle\Database\DatabaseSetup;
use Pimcore\Console\Application;
use Pimcore\Test\KernelTestCase;

final class PimcoreDatabaseInstallerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        if (!class_exists(DatabaseSetup::class)) {
            self::markTestSkipped('PimcoreDatabaseInstaller only supports Pimcore ^2026.1.');
        }
    }

    /** @test */
    #[Test]
    public function it_installs_the_schema_and_seed_data_with_an_admin_user(): void
    {
        $connection = $this->freshDatabase();

        (new PimcoreDatabaseInstaller())->install($connection, insertSeedData: true);

        self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM assets'));
        self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM documents'));
        self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM objects'));

        $users = $connection->fetchAllAssociative('SELECT name FROM users ORDER BY id');
        self::assertSame(['system', 'admin'], array_column($users, 'name'));
    }

    /** @test */
    #[Test]
    public function it_installs_the_schema_without_seed_data(): void
    {
        $connection = $this->freshDatabase();

        (new PimcoreDatabaseInstaller())->install($connection, insertSeedData: false);

        // The schema itself is always created, but the seed data (root nodes) is skipped - a dump
        // imported afterward is expected to provide it instead.
        self::assertCount(0, $connection->fetchAllNumeric('SELECT * FROM assets'));
        self::assertCount(0, $connection->fetchAllNumeric('SELECT * FROM documents'));
        self::assertCount(0, $connection->fetchAllNumeric('SELECT * FROM objects'));

        $users = $connection->fetchAllAssociative('SELECT name FROM users ORDER BY id');
        self::assertSame(['system', 'admin'], array_column($users, 'name'));
    }

    /** @test */
    #[Test]
    public function it_installs_the_admin_user_with_given_credentials(): void
    {
        $connection = $this->freshDatabase();

        (new PimcoreDatabaseInstaller())->install(
            $connection,
            insertSeedData: false,
            adminCredentials: ['username' => 'test-admin', 'password' => 'test-password'],
        );

        $users = $connection->fetchAllAssociative('SELECT name, password FROM users ORDER BY id');
        self::assertSame(['system', 'test-admin'], array_column($users, 'name'));
        self::assertNotSame('', $users[1]['password']);
    }

    private function freshDatabase(): Connection
    {
        $application = new Application(self::bootKernel());
        $application->setAutoExit(false);

        /** @var ManagerRegistry $registry */
        $registry = self::getContainer()->get('doctrine');

        $runCommand = new RunCommand($application);
        $runCommand('doctrine:database:drop', [
            '--connection' => $registry->getDefaultConnectionName(),
            '--if-exists' => true,
            '--force' => true,
        ]);
        $runCommand('doctrine:database:create', [
            '--connection' => $registry->getDefaultConnectionName(),
        ]);

        /** @var Connection $connection */
        $connection = $registry->getConnection();

        return $connection;
    }
}
