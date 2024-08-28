<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Internal\KernelConfigurator;

abstract class KernelTestCase extends \Pimcore\Test\KernelTestCase
{
    /**
     * @param array{config?: callable(TestKernel):void, environment?: string, debug?: bool, ...} $options
     */
    protected static function createKernel(array $options = []): TestKernel
    {
        $kernel = parent::createKernel($options);

        if (!$kernel instanceof TestKernel) {
            throw new \LogicException(sprintf('Kernel must be an instance of %s', TestKernel::class));
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
