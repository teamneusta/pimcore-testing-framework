<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\KernelTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\Attribute\ConfigureConfigurationBundle;

final class CustomAttributeTest extends KernelTestCase
{
    /**
     * @test
     */
    #[ConfigureConfigurationBundle(['foo' => 'value1'])]
    public function configuration_via_attribute(): void
    {
        $container = self::getContainer();

        self::assertSame('value1', $container->getParameter('configuration.foo'));
        self::assertSame(['value2', 'value3'], $container->getParameter('configuration.bar'));
    }

    public function provideData(): iterable
    {
        yield [new ConfigureConfigurationBundle(['foo' => 'test1', 'bar' => ['test2', 'test3']])];
    }

    /**
     * @test
     *
     * @dataProvider provideData
     */
    public function configuration_via_data_provider(): void
    {
        $container = self::getContainer();

        self::assertSame('test1', $container->getParameter('configuration.foo'));
        self::assertSame(['test2', 'test3'], $container->getParameter('configuration.bar'));
    }
}
