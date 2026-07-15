<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Neusta\Pimcore\TestingFramework\Database\DatabaseResetter;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Test\KernelTestCase;

final class DatabaseResetterTest extends KernelTestCase
{
    /**
     * This repo's own phpunit.xml.dist bootstraps DAMA's PHPUnitExtension, which unconditionally
     * sets `StaticDriver::setKeepStaticConnections(true)` for the whole test run - regardless of
     * whether the DAMA bundle itself is registered in the kernel (it isn't, here). As a result,
     * `DatabaseResetter::resetSchema()`'s real reset path is normally never exercised by this
     * suite. Both tests below explicitly force the flag to prove each branch for real.
     */
    private bool $originalKeepStaticConnections;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalKeepStaticConnections = StaticDriver::isKeepStaticConnections();
    }

    protected function tearDown(): void
    {
        StaticDriver::setKeepStaticConnections($this->originalKeepStaticConnections);

        parent::tearDown();
    }

    /** @test */
    #[Test]
    public function reset_schema_is_skipped_while_dama_is_active(): void
    {
        $kernel = self::bootKernel();
        $connection = $this->getConnection();

        DatabaseResetter::resetDatabase($kernel);
        $connection->insert('users', ['type' => 'user', 'name' => 'reset-schema-dama-marker']);

        StaticDriver::setKeepStaticConnections(true);
        DatabaseResetter::resetSchema($kernel);

        self::assertSame(
            1,
            (int) $connection->fetchOne('SELECT COUNT(*) FROM users WHERE name = ?', ['reset-schema-dama-marker']),
        );

        // Leave a clean baseline behind - do not rely on declaration order for cleanup.
        DatabaseResetter::resetDatabase($kernel);
    }

    /** @test */
    #[Test]
    public function reset_schema_actually_resets_the_schema_while_dama_is_inactive(): void
    {
        $kernel = self::bootKernel();
        $connection = $this->getConnection();

        DatabaseResetter::resetDatabase($kernel);
        $connection->insert('users', ['type' => 'user', 'name' => 'reset-schema-marker']);

        StaticDriver::setKeepStaticConnections(false);
        DatabaseResetter::resetSchema($kernel);

        self::assertSame(
            0,
            (int) $connection->fetchOne('SELECT COUNT(*) FROM users WHERE name = ?', ['reset-schema-marker']),
        );
        // Baseline reinstalled: system + admin user present again.
        self::assertSame(2, (int) $connection->fetchOne('SELECT COUNT(*) FROM users'));
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
