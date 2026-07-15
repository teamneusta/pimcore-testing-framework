<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Database;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Neusta\Pimcore\TestingFramework\Database\DatabaseResetter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class DatabaseResetterTest extends TestCase
{
    use ProphecyTrait;

    private bool $originalKeepStaticConnections;

    protected function setUp(): void
    {
        $this->originalKeepStaticConnections = StaticDriver::isKeepStaticConnections();
    }

    protected function tearDown(): void
    {
        StaticDriver::setKeepStaticConnections($this->originalKeepStaticConnections);
        unset($_SERVER['DISABLE_DATABASE_RESET']);
    }

    /** @test */
    #[Test]
    public function has_been_reset_is_forced_by_the_disable_env_var(): void
    {
        $_SERVER['DISABLE_DATABASE_RESET'] = '1';

        self::assertTrue(DatabaseResetter::hasBeenReset());
    }

    /** @test */
    #[Test]
    public function is_dama_doctrine_test_bundle_enabled_reflects_the_static_driver_flag(): void
    {
        StaticDriver::setKeepStaticConnections(true);
        self::assertTrue(DatabaseResetter::isDAMADoctrineTestBundleEnabled());

        StaticDriver::setKeepStaticConnections(false);
        self::assertFalse(DatabaseResetter::isDAMADoctrineTestBundleEnabled());
    }

    /** @test */
    #[Test]
    public function reset_database_is_a_no_op_without_a_doctrine_service(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('doctrine')->willReturn(false);
        $container->get(Argument::any())->shouldNotBeCalled();

        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getContainer()->willReturn($container->reveal());

        // Must not throw despite no doctrine service being available.
        DatabaseResetter::resetDatabase($kernel->reveal());

        $container->has('doctrine')->shouldHaveBeenCalled();
    }

    /** @test */
    #[Test]
    public function reset_schema_is_a_no_op_without_a_doctrine_service(): void
    {
        StaticDriver::setKeepStaticConnections(false);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('doctrine')->willReturn(false);
        $container->get(Argument::any())->shouldNotBeCalled();

        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getContainer()->willReturn($container->reveal());

        DatabaseResetter::resetSchema($kernel->reveal());

        $container->has('doctrine')->shouldHaveBeenCalled();
    }

    /** @test */
    #[Test]
    public function reset_schema_never_touches_the_kernel_when_dama_is_active(): void
    {
        StaticDriver::setKeepStaticConnections(true);

        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getContainer()->shouldNotBeCalled();

        DatabaseResetter::resetSchema($kernel->reveal());
    }
}
