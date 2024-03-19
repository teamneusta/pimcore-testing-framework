<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\RegisterBundle;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;

final class ExtensionConfigurationTest extends ConfigurableKernelTestCase
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

        self::assertEquals('value1', $container->getParameter('configuration.foo'));
        self::assertEquals(['value2', 'value3'], $container->getParameter('configuration.bar'));
    }

    /**
     * @test
     */
    #[RegisterBundle(ConfigurationBundle::class), ConfigureExtension('configuration', [
        'foo' => 'value1',
        'bar' => ['value2', 'value3'],
    ])]
    public function extension_configuration_via_attributes(): void
    {
        $container = self::getContainer();

        self::assertEquals('value1', $container->getParameter('configuration.foo'));
        self::assertEquals(['value2', 'value3'], $container->getParameter('configuration.bar'));
    }
}
