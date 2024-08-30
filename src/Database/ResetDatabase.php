<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Pimcore\Test\KernelTestCase;

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

        $kernel = static::createKernel();
        $kernel->boot();

        DatabaseResetter::resetDatabase($kernel);

        if ($isDAMADoctrineTestBundleEnabled) {
            // re-enable static connections
            StaticDriver::setKeepStaticConnections(true);
        }

        $kernel->shutdown();
    }

    /**
     * @internal
     *
     * @before
     */
    public static function _resetSchema(): void
    {
        if (!is_subclass_of(static::class, KernelTestCase::class)) {
            throw DoesNotExtendKernelTestCase::forTrait(__TRAIT__);
        }

        $kernel = static::createKernel();
        $kernel->boot();

        DatabaseResetter::resetSchema($kernel);

        $kernel->shutdown();
    }
}
