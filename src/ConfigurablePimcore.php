<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Internal\PimcoreConfigurator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait ConfigurablePimcore
{
    /**
     * @internal
     *
     * @before
     */
    public function _applyPimcoreConfigurations(): void
    {
        PimcoreConfigurator::apply($this, $this instanceof KernelTestCase && static::$booted);
    }

    /**
     * @internal
     *
     * @after
     */
    public function _resetPimcoreConfigurations(): void
    {
        PimcoreConfigurator::reset($this instanceof KernelTestCase && static::$booted);
    }
}
