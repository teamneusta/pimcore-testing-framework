<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test;

use Neusta\Pimcore\TestingFramework\Test\Attribute\KernelConfiguration;
use Neusta\Pimcore\TestingFramework\TestKernel;
use PHPUnit\Framework\TestCase;
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
        $class = new \ReflectionClass($this);
        $method = $class->getMethod($this->getName(false));
        $providedData = $this->getProvidedData();
        $configurations = [];

        foreach ($class->getAttributes(KernelConfiguration::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $configurations[] = $attribute->newInstance();
        }

        foreach ($method->getAttributes(KernelConfiguration::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $configurations[] = $attribute->newInstance();
        }

        if ([] !== $providedData) {
            foreach ($providedData as $data) {
                if ($data instanceof KernelConfiguration) {
                    $configurations[] = $data;
                }
            }

            // remove them from the arguments passed to the test method
            (new \ReflectionProperty(TestCase::class, 'data'))->setValue($this, array_values(array_filter(
                $providedData,
                fn ($data) => !$data instanceof KernelConfiguration,
            )));
        }

        self::$kernelConfigurations = $configurations;
    }

    protected function tearDown(): void
    {
        self::$kernelConfigurations = [];
        parent::tearDown();
    }
}
