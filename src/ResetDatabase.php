<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Neusta\Pimcore\TestingFramework\Internal\Database\DatabaseResetter;
use Neusta\Pimcore\TestingFramework\Internal\IsolatedKernel;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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

        $kernel = IsolatedKernel::boot(static::createKernel(...));

        try {
            DatabaseResetter::resetDatabase($kernel->kernel);

            if ($isDAMADoctrineTestBundleEnabled) {
                // re-enable static connections
                StaticDriver::setKeepStaticConnections(true);
            }
        } finally {
            $kernel->shutdown();
        }
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

        $kernel = IsolatedKernel::boot(static::createKernel(...));

        try {
            DatabaseResetter::resetSchema($kernel->kernel);
        } finally {
            $kernel->shutdown();
        }
    }
}
