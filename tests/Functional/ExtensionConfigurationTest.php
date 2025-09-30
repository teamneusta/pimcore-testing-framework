<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Attribute\Kernel\RegisterBundle;
use Neusta\Pimcore\TestingFramework\ConfigurableKernel;
use Neusta\Pimcore\TestingFramework\TestKernel;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;
use Pimcore\Test\KernelTestCase;

final class ExtensionConfigurationTest extends KernelTestCase
{
    use ConfigurableKernel;

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

        self::assertSame('value1', $container->getParameter('configuration.foo'));
        self::assertSame(['value2', 'value3'], $container->getParameter('configuration.bar'));
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

        self::assertSame('value1', $container->getParameter('configuration.foo'));
        self::assertSame(['value2', 'value3'], $container->getParameter('configuration.bar'));
    }
}
