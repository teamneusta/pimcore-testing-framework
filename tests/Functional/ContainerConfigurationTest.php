<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureContainer;
use Neusta\Pimcore\TestingFramework\Test\Attribute\RegisterBundle;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[RegisterBundle(ConfigurationBundle::class)]
final class ContainerConfigurationTest extends ConfigurableKernelTestCase
{
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
        self::bootKernel(['config' => fn (TestKernel $kernel) => $kernel->addTestConfig($config)]);

        self::assertContainerConfiguration(self::getContainer());
    }

    public function provideDifferentConfigurationFormatsViaKernelConfigurationObject(): iterable
    {
        yield 'YAML' => [new ConfigureContainer(__DIR__ . '/../Fixtures/Resources/ConfigurationBundle/config.yaml')];
        yield 'XML' => [new ConfigureContainer(__DIR__ . '/../Fixtures/Resources/ConfigurationBundle/config.xml')];
        yield 'PHP' => [new ConfigureContainer(__DIR__ . '/../Fixtures/Resources/ConfigurationBundle/config.php')];
        yield 'Callable' => [new ConfigureContainer(function (ContainerBuilder $container) {
            $container->loadFromExtension('configuration', [
                'foo' => 'value1',
                'bar' => ['value2', 'value3'],
            ]);

            $container->register('something', \stdClass::class)->setPublic(true);
        })];
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentConfigurationFormatsViaKernelConfigurationObject
     */
    public function different_configuration_formats_via_data_provider(): void
    {
        self::assertContainerConfiguration(self::getContainer());
    }

    /**
     * @test
     */
    #[ConfigureContainer(__DIR__ . '/../Fixtures/Resources/ConfigurationBundle/config.yaml')]
    public function configuration_in_yaml_via_attribute(): void
    {
        self::assertContainerConfiguration(self::getContainer());
    }

    /**
     * @test
     */
    #[ConfigureContainer(__DIR__ . '/../Fixtures/Resources/ConfigurationBundle/config.xml')]
    public function configuration_in_xml_via_attribute(): void
    {
        self::assertContainerConfiguration(self::getContainer());
    }

    /**
     * @test
     */
    #[ConfigureContainer(__DIR__ . '/../Fixtures/Resources/ConfigurationBundle/config.php')]
    public function configuration_in_php_via_attribute(): void
    {
        self::assertContainerConfiguration(self::getContainer());
    }

    public static function assertContainerConfiguration(ContainerInterface $container): void
    {
        self::assertSame('value1', $container->getParameter('configuration.foo'));
        self::assertSame(['value2', 'value3'], $container->getParameter('configuration.bar'));
        self::assertInstanceOf(\stdClass::class, $container->get('something', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }
}
