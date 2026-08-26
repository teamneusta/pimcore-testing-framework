<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Internal\PimcoreConfigurator;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
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
    #[BeforeClass]
    public static function _setUpPimcoreConfigurations(): void
    {
        if (is_subclass_of(static::class, KernelTestCase::class)) {
            PimcoreConfigurator::useKernel(static::bootKernel(...), static::ensureKernelShutdown(...));
        } else {
            PimcoreConfigurator::useKernel();
        }
    }

    /**
     * @internal
     *
     * @before
     */
    #[Before]
    public function _applyPimcoreConfigurations(): void
    {
        PimcoreConfigurator::collect($this);
        PimcoreConfigurator::apply();
    }

    /**
     * @internal
     *
     * @after
     */
    #[After]
    public function _resetPimcoreConfigurations(): void
    {
        PimcoreConfigurator::reset();
    }
}
