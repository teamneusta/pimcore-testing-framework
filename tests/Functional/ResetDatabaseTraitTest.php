<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Neusta\Pimcore\TestingFramework\ResetDatabase;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Test\KernelTestCase;

/**
 * Exercises `ResetDatabase` through its real public surface - the way consumer bundles actually use
 * it - rather than manually instantiating its internal collaborators (see `ResetDatabaseTest` for that
 * and `DatabaseResetterTest` for the resetSchema()-branch coverage that can't be observed this way, since
 * `DatabaseResetter::$hasBeenReset` is a process-wide flag and PHPUnit runs classes without interleaving).
 */
final class ResetDatabaseTraitTest extends KernelTestCase
{
    use ResetDatabase;

    /** @test */
    #[Test]
    public function it_installs_a_baseline_pimcore_database(): void
    {
        $connection = $this->getConnection();

        self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM assets'));
        self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM documents'));
        self::assertCount(1, $connection->fetchAllNumeric('SELECT * FROM objects'));

        $users = $connection->fetchAllAssociative('SELECT name FROM users ORDER BY id');
        self::assertSame(['system', 'admin'], array_column($users, 'name'));
    }

    /** @test */
    #[Test]
    public function it_boots_cleanly_for_a_second_test_in_the_same_class(): void
    {
        // The #[Before] _resetSchema() hook must run again without error for every test method.
        self::assertCount(1, $this->getConnection()->fetchAllNumeric('SELECT * FROM assets'));
    }

    private function getConnection(): Connection
    {
        /** @var ManagerRegistry $registry */
        $registry = self::getContainer()->get('doctrine');

        /** @var Connection $connection */
        $connection = $registry->getConnection();

        return $connection;
    }
}
