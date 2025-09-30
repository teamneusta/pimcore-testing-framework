<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Neusta\Pimcore\TestingFramework\Database\PimcoreDatabaseResetter;
use Pimcore\Console\Application;
use Pimcore\Test\KernelTestCase;
use Pimcore\Version;

final class ResetDatabaseTest extends KernelTestCase
{
    /**
     * @test
     *
     * @dataProvider databaseResetModeProvider
     */
    public function it_resets_database(string $dumpLocation): void
    {
        $_SERVER['DATABASE_DUMP_LOCATION'] = $dumpLocation;

        $application = new Application(self::bootKernel());
        $application->setAutoExit(false);

        /** @var ManagerRegistry $registry */
        $registry = self::getContainer()->get('doctrine');

        /** @var Connection $connection */
        $connection = $registry->getConnection();

        $resetter = new PimcoreDatabaseResetter($application, $registry);
        $resetter->resetDatabase();

        self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM assets'));
        self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM documents'));
        self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM objects'));
        self::assertCount(2, $users = $connection->fetchAllAssociative('SELECT * FROM users'));
        self::assertSame('system', $users[0]['name']);
        self::assertSame('admin', $users[1]['name']);
    }

    public function databaseResetModeProvider(): iterable
    {
        yield 'Default mode' => [''];
        yield 'Dump mode' => [self::isPimcore10() ? 'dump-10' : 'dump'];
    }

    private static function isPimcore10(): bool
    {
        return !method_exists(Version::class, 'getMajorVersion') || 10 === Version::getMajorVersion();
    }
}
