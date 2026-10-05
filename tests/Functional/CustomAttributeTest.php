<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\ConfigurableKernel;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\Attribute\ConfigureConfigurationBundle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Test\KernelTestCase;

final class CustomAttributeTest extends KernelTestCase
{
    use ConfigurableKernel;

    /** @test */
    #[Test]
    #[ConfigureConfigurationBundle(['foo' => 'value1'])]
    public function configuration_via_attribute(): void
    {
        $container = self::getContainer();

        self::assertSame('value1', $container->getParameter('configuration.foo'));
        self::assertSame(['value2', 'value3'], $container->getParameter('configuration.bar'));
    }

    public static function provideData(): iterable
    {
        yield [new ConfigureConfigurationBundle(['foo' => 'test1', 'bar' => ['test2', 'test3']])];
    }

    /**
     * @test
     *
     * @dataProvider provideData
     */
    #[Test]
    // The second argument is `validateArgumentCount` (PHPUnit 12; older versions ignore the extra argument): it
    // keeps PHPUnit from warning about the configuration objects this library strips from the provided data.
    #[DataProvider('provideData', false)]
    public function configuration_via_data_provider(): void
    {
        $container = self::getContainer();

        self::assertSame('test1', $container->getParameter('configuration.foo'));
        self::assertSame(['test2', 'test3'], $container->getParameter('configuration.bar'));
    }
}
