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

/**
 * `PimcoreDatabaseResetter::resetSchema()` (and its private `dropSchema()`) is what
 * `ResetDatabase::_resetSchema()` calls before every single test in a consumer project - but this
 * library's own suite registers `dama/doctrine-test-bundle` (see phpunit.xml.dist), so
 * `DatabaseResetter::resetSchema()` short-circuits to a no-op before ever reaching it (see
 * DatabaseResetterTest for that dispatch logic). That left the actual schema-only reset entirely
 * unexercised. This test calls the resetter directly, the same way ResetDatabaseTest does for
 * resetDatabase(), bypassing DAMA entirely.
 *
 * Note: `dropSchema()`'s and `createSchema()`'s `$this->registry->getDefaultManagerName()` branch (the
 * `doctrine:schema:drop`/`doctrine:schema:update` calls scoped by `DoctrineSchemaAssetFilter`) stays
 * unreached here regardless - `tests/app` has no Doctrine ORM entities of its own, so
 * `getDefaultManagerName()` is always empty in this environment. That branch exists for consumer
 * bundles that register their own ORM entities alongside Pimcore; exercising it here would need a
 * dedicated ORM entity fixture in `tests/app`, which is a separate, larger piece of work. The filtering
 * logic itself (`DoctrineSchemaAssetFilter`) is unit-tested directly with faked ORM 2/3 mappings.
 */
final class ResetSchemaTest extends KernelTestCase
{
    /**
     * @test
     *
     * @dataProvider databaseResetModeProvider
     */
    #[Test]
    #[DataProvider('databaseResetModeProvider')]
    public function it_resets_the_schema_without_reinstalling_the_whole_database(string $dumpLocation): void
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

            // Establish a known baseline first, via the already-covered resetDatabase() path.
            $resetter->resetDatabase();

            // The path under test: a consumer's ResetDatabase trait calls resetSchema() before every
            // single test, so it has to reliably reproduce the same baseline on its own, repeatedly,
            // without dropping and recreating the whole database each time.
            foreach ([1, 2] as $attempt) {
                $resetter->resetSchema();

                self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM assets'), "attempt {$attempt}");
                self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM documents'), "attempt {$attempt}");
                self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM objects'), "attempt {$attempt}");
                self::assertCount(2, $users = $connection->fetchAllAssociative('SELECT * FROM users'), "attempt {$attempt}");
                self::assertSame('system', $users[0]['name']);
                self::assertSame('admin', $users[1]['name']);
            }
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
