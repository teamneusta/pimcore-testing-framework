<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Internal;

use Neusta\Pimcore\TestingFramework\Attribute\ConfigureKernel;
use Neusta\Pimcore\TestingFramework\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @internal */
final class KernelConfigurator
{
    /** @var list<ConfigureKernel> */
    private static array $configurators = [];

    public static function up(KernelTestCase $testCase): void
    {
        self::$configurators = AttributeProvider::getAttributes($testCase, ConfigureKernel::class);
    }

    public static function configure(TestKernel $kernel): void
    {
        foreach (self::$configurators as $configurator) {
            $configurator->configure($kernel);
        }
    }

    public static function down(): void
    {
        self::$configurators = [];
    }
}
