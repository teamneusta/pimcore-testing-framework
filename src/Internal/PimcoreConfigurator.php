<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Internal;

use Neusta\Pimcore\TestingFramework\Attribute\ConfigurePimcore;
use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use PHPUnit\Framework\TestCase;

/** @internal */
final class PimcoreConfigurator
{
    private static ?\Closure $bootKernel = null;
    private static ?\Closure $shutdownKernel = null;

    /** @var list<ConfigurePimcore> */
    private static array $configurators = [];

    public static function setUp(?\Closure $bootKernel = null, ?\Closure $shutdownKernel = null): void
    {
        self::$bootKernel = $bootKernel;
        self::$shutdownKernel = $shutdownKernel;
    }

    public static function apply(TestCase $testCase): void
    {
        self::$configurators = AttributeProvider::getAttributes($testCase, ConfigurePimcore::class);

        foreach (self::iterateConfigurators(self::$configurators) as $configurator) {
            $configurator->apply();
        }
    }

    public static function reset(): void
    {
        foreach (self::iterateConfigurators(array_reverse(self::$configurators)) as $configurator) {
            $configurator->reset();
        }

        self::$configurators = [];
    }

    /**
     * @param list<ConfigurePimcore> $configurators
     */
    public static function iterateConfigurators(array $configurators): \Generator
    {
        $kernel = null;

        foreach ($configurators as $configurator) {
            if (!$kernel && $configurator::requiresBootedKernel()) {
                if (!self::$bootKernel) {
                    throw DoesNotExtendKernelTestCase::forAttribute($configurator::class);
                }

                $kernel = (self::$bootKernel)();
            }

            yield $configurator;
        }

        if ($kernel && self::$shutdownKernel) {
            (self::$shutdownKernel)();
        }
    }
}
