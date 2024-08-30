<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Internal;

use Neusta\Pimcore\TestingFramework\Attribute\ConfigurePimcore;
use Neusta\Pimcore\TestingFramework\Exception\KernelIsNotBooted;
use PHPUnit\Framework\TestCase;

/** @internal */
final class PimcoreConfigurator
{
    /** @var list<ConfigurePimcore> */
    private static array $configurators = [];

    public static function apply(TestCase $testCase, bool $isBooted): void
    {
        self::$configurators = AttributeProvider::getAttributes($testCase, ConfigurePimcore::class);

        foreach (self::$configurators as $configurator) {
            if (!$isBooted && $configurator::requiresBootedKernel()) {
                throw KernelIsNotBooted::forAttribute($configurator::class);
            }

            $configurator->apply();
        }
    }

    public static function reset(bool $isBooted): void
    {
        foreach (array_reverse(self::$configurators) as $configurator) {
            if (!$isBooted && $configurator::requiresBootedKernel()) {
                throw KernelIsNotBooted::forAttribute($configurator::class);
            }

            $configurator->reset();
        }

        self::$configurators = [];
    }
}
