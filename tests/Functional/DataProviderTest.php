<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Attribute\Kernel\RegisterBundle;
use Neusta\Pimcore\TestingFramework\KernelTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;

final class DataProviderTest extends KernelTestCase
{
    public function provideData(): iterable
    {
        yield 'kernel configuration at the beginning' => [
            new RegisterBundle(ConfigurationBundle::class),
            new ConfigureExtension('configuration', [
                'foo' => 'value1',
                'bar' => ['value2', 'value3'],
            ]),
            'value1',
            'value2',
            'value3',
        ];

        yield 'kernel configuration at the end' => [
            'foo',
            'bar',
            'baz',
            new RegisterBundle(ConfigurationBundle::class),
            new ConfigureExtension('configuration', [
                'foo' => 'foo',
                'bar' => ['bar', 'baz'],
            ]),
        ];

        yield 'kernel configuration in between other provided data' => [
            'test1',
            new RegisterBundle(ConfigurationBundle::class),
            'test2',
            new ConfigureExtension('configuration', [
                'foo' => 'test1',
                'bar' => ['test2', 'test3'],
            ]),
            'test3',
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideData
     */
    public function configuration_via_data_provider(string $value1, string $value2, string $value3): void
    {
        $container = self::getContainer();

        self::assertSame($value1, $container->getParameter('configuration.foo'));
        self::assertSame([$value2, $value3], $container->getParameter('configuration.bar'));
    }
}
