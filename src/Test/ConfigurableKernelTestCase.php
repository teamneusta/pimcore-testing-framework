<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\Attribute\KernelConfiguration;
use Neusta\Pimcore\TestingFramework\Test\Reflection\TestAttributeProvider;
use Pimcore\Test\KernelTestCase;

abstract class ConfigurableKernelTestCase extends KernelTestCase
{
    /** @var list<KernelConfiguration> */
    private static iterable $kernelConfigurations = [];

    /**
     * @param array{config?: callable(TestKernel):void, environment?: string, debug?: bool, ...} $options
     */
    protected static function createKernel(array $options = []): TestKernel
    {
        $kernel = parent::createKernel($options);
        \assert($kernel instanceof TestKernel);

        foreach (self::$kernelConfigurations as $configuration) {
            $configuration->configure($kernel);
        }

        $kernel->handleOptions($options);

        return $kernel;
    }

    /**
     * @internal
     *
     * @before
     */
    public function _getKernelConfigurationFromAttributes(): void
    {
        self::$kernelConfigurations = (new TestAttributeProvider($this))->getKernelConfigurationAttributes();
    }

    protected function tearDown(): void
    {
        self::$kernelConfigurations = [];
        parent::tearDown();
    }
}
