<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Neusta\Pimcore\TestingFramework\Internal\KernelConfigurator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait ConfigurableKernel
{
    /**
     * @param array{config?: callable(TestKernel):void, environment?: string, debug?: bool, ...} $options
     */
    protected static function createKernel(array $options = []): TestKernel
    {
        if (!is_subclass_of(static::class, KernelTestCase::class)) {
            throw DoesNotExtendKernelTestCase::forTrait(__TRAIT__);
        }

        $kernel = parent::createKernel($options);

        if (!$kernel instanceof TestKernel) {
            throw new \LogicException(\sprintf('Kernel must be an instance of %s', TestKernel::class));
        }

        KernelConfigurator::configure($kernel);

        $kernel->handleOptions($options);

        return $kernel;
    }

    /**
     * @internal
     *
     * @before
     */
    public function _collectKernelConfigurations(): void
    {
        if (!$this instanceof KernelTestCase) {
            throw DoesNotExtendKernelTestCase::forTrait(__TRAIT__);
        }

        KernelConfigurator::up($this);
    }

    /**
     * @internal
     *
     * @after
     */
    public function _resetKernelConfigurations(): void
    {
        KernelConfigurator::down();
    }
}
