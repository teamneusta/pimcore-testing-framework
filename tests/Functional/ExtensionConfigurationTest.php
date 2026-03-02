<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\RegisterBundle;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;
use PHPUnit\Framework\Attributes\Test;

final class ExtensionConfigurationTest extends ConfigurableKernelTestCase
{
    #[Test]
    public function extension_configuration(): void
    {
        self::bootKernel(['config' => static function (TestKernel $kernel) {
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

    #[Test]
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
