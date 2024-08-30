<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Attribute\ConfigureKernel;
use PHPUnit\Framework\TestCase;

abstract class KernelTestCase extends \Pimcore\Test\KernelTestCase
{
    /** @var list<ConfigureKernel> */
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

        foreach ($class->getAttributes(ConfigureKernel::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $configurations[] = $attribute->newInstance();
        }

        foreach ($method->getAttributes(ConfigureKernel::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $configurations[] = $attribute->newInstance();
        }

        if ([] !== $providedData) {
            foreach ($providedData as $data) {
                if ($data instanceof ConfigureKernel) {
                    $configurations[] = $data;
                }
            }

            // remove them from the arguments passed to the test method
            (new \ReflectionProperty(TestCase::class, 'data'))->setValue($this, array_values(array_filter(
                $providedData,
                fn ($data) => !$data instanceof ConfigureKernel,
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
