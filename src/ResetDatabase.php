<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Neusta\Pimcore\TestingFramework\Internal\Database\DatabaseResetter;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @mixin KernelTestCase
 */
trait ResetDatabase
{
    /**
     * @internal
     *
     * @beforeClass
     */
    #[BeforeClass]
    public static function _resetDatabase(): void
    {
        if (DatabaseResetter::hasBeenReset()) {
            return;
        }

        if (!is_subclass_of(static::class, KernelTestCase::class)) {
            throw DoesNotExtendKernelTestCase::forTrait(__TRAIT__);
        }

        if ($isDAMADoctrineTestBundleEnabled = DatabaseResetter::isDAMADoctrineTestBundleEnabled()) {
            // disable static connections for this operation
            StaticDriver::setKeepStaticConnections(false);
        }

        $previousKernel = \Pimcore::getKernel();
        $kernel = static::createKernel();
        $kernel->boot();

        DatabaseResetter::resetDatabase($kernel);

        if ($isDAMADoctrineTestBundleEnabled) {
            // re-enable static connections
            StaticDriver::setKeepStaticConnections(true);
        }

        self::_shutdownDedicatedKernel($kernel, $previousKernel);
    }

    /**
     * @internal
     *
     * @before
     */
    #[Before]
    protected function _resetSchema(): void
    {
        if (!is_subclass_of(static::class, KernelTestCase::class)) {
            throw DoesNotExtendKernelTestCase::forTrait(__TRAIT__);
        }

        $previousKernel = \Pimcore::getKernel();
        $kernel = static::createKernel();
        $kernel->boot();

        DatabaseResetter::resetSchema($kernel);

        self::_shutdownDedicatedKernel($kernel, $previousKernel);
    }

    /**
     * `Pimcore\Test\KernelTestCase::createKernel()` registers every new kernel as Pimcore's static one. Point it
     * back to the previous kernel afterwards, or Pimcore keeps resolving its container through the kernel shut down
     * here - while the test may boot or still use another one. Shut down first: Pimcore's `Kernel::shutdown()`
     * resolves services through its static kernel, which must still be this one at that point.
     *
     * @internal
     */
    private static function _shutdownDedicatedKernel(KernelInterface $kernel, ?KernelInterface $previousKernel): void
    {
        $kernel->shutdown();

        if ($previousKernel) {
            \Pimcore::setKernel($previousKernel);
        }
    }
}
