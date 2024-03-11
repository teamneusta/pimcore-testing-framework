<?php
declare(strict_types=1);

namespace Tests\Functional\Neusta\Pimcore\TestingFramework;

use Fixtures\ConfigurationBundle\ConfigurationBundle;
use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class BundleConfigurationTest extends ConfigurableKernelTestCase
{
    /**
     * @test
     */
    public function extension_configuration(): void
    {
        self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestBundle(ConfigurationBundle::class);
            $kernel->addTestExtensionConfig('configuration', [
                'foo' => 'value1',
                'bar' => ['value2', 'value3'],
            ]);
        }]);

        $container = self::getContainer();

        $this->assertEquals('value1', $container->getParameter('configuration.foo'));
        $this->assertEquals(['value2', 'value3'], $container->getParameter('configuration.bar'));
    }

    public function provideDifferentConfigurationFormats(): iterable
    {
        yield 'YAML' => [__DIR__ . '/../Fixtures/Resources/ConfigurationBundle/config.yaml'];
        yield 'XML' => [__DIR__ . '/../Fixtures/Resources/ConfigurationBundle/config.xml'];
        yield 'PHP' => [__DIR__ . '/../Fixtures/Resources/ConfigurationBundle/config.php'];
        yield 'Callable' => [function (ContainerBuilder $container) {
            $container->loadFromExtension('configuration', [
                'foo' => 'value1',
                'bar' => ['value2', 'value3'],
            ]);

            $container->register('something', \stdClass::class)->setPublic(true);
        }];
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentConfigurationFormats
     */
    public function different_configuration_formats(string|callable $config): void
    {
        self::bootKernel(['config' => function (TestKernel $kernel) use ($config) {
            $kernel->addTestBundle(ConfigurationBundle::class);
            $kernel->addTestConfig($config);
        }]);

        $container = self::getContainer();

        $this->assertEquals('value1', $container->getParameter('configuration.foo'));
        $this->assertEquals(['value2', 'value3'], $container->getParameter('configuration.bar'));
        self::assertInstanceOf(\stdClass::class, $container->get('something', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }
}
