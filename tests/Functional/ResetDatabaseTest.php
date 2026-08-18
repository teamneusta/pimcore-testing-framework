<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Neusta\Pimcore\TestingFramework\Internal\Database\PimcoreDatabaseResetter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Console\Application;
use Pimcore\Test\KernelTestCase;

final class ResetDatabaseTest extends KernelTestCase
{
    /**
     * @test
     *
     * @dataProvider databaseResetModeProvider
     */
    #[Test]
    #[DataProvider('databaseResetModeProvider')]
    public function it_resets_database(string $dumpLocation): void
    {
        $_SERVER['DATABASE_DUMP_LOCATION'] = $dumpLocation;

        try {
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
        } finally {
            unset($_SERVER['DATABASE_DUMP_LOCATION']);
        }
    }

    public static function databaseResetModeProvider(): iterable
    {
        yield 'Default mode' => [''];
        yield 'Dump mode' => ['dump'];
    }
}
