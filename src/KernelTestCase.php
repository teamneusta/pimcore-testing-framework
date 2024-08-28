<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Attribute\ConfigureKernel;
use Neusta\Pimcore\TestingFramework\Internal\AttributeProvider;

abstract class KernelTestCase extends \Pimcore\Test\KernelTestCase
{
    /**
     * @internal
     *
     * @var list<ConfigureKernel>
     */
    private static array $kernelConfigurations = [];

    /**
     * @param array{config?: callable(TestKernel):void, environment?: string, debug?: bool, ...} $options
     */
    protected static function createKernel(array $options = []): TestKernel
    {
        $kernel = parent::createKernel($options);

        if (!$kernel instanceof TestKernel) {
            throw new \LogicException(sprintf('Kernel must be an instance of %s', TestKernel::class));
        }

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
    public function _collectKernelConfigurations(): void
    {
        self::$kernelConfigurations = AttributeProvider::getAttributes($this, ConfigureKernel::class);
    }

    /**
     * @internal
     *
     * @after
     */
    public function _resetKernelConfigurations(): void
    {
        self::$kernelConfigurations = [];
    }
}
