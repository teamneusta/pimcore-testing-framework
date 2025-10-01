<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Internal\PimcoreConfigurator;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin TestCase
 */
trait ConfigurablePimcore
{
    /**
     * @internal
     *
     * @beforeClass
     */
    public static function _setUpPimcoreConfigurations(): void
    {
        if (is_subclass_of(static::class, KernelTestCase::class)) {
            PimcoreConfigurator::setUp(static::bootKernel(...), static::ensureKernelShutdown(...));
        } else {
            PimcoreConfigurator::setUp();
        }
    }

    /**
     * @internal
     *
     * @before
     */
    public function _applyPimcoreConfigurations(): void
    {
        PimcoreConfigurator::apply($this);
    }

    /**
     * @internal
     *
     * @after
     */
    public function _resetPimcoreConfigurations(): void
    {
        PimcoreConfigurator::reset();
    }
}
