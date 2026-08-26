<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Internal;

use Neusta\Pimcore\TestingFramework\KernelConfiguration;
use Neusta\Pimcore\TestingFramework\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @internal */
final class KernelConfigurator
{
    /** @var list<KernelConfiguration> */
    private static array $configurators = [];

    public static function collect(KernelTestCase $testCase): void
    {
        self::$configurators = AttributeProvider::getAttributes($testCase, KernelConfiguration::class);
    }

    public static function apply(TestKernel $kernel): void
    {
        foreach (self::$configurators as $configurator) {
            $configurator->configure($kernel);
        }
    }

    public static function reset(): void
    {
        self::$configurators = [];
    }
}
