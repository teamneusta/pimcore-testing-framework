<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Internal;

use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;
use PHPUnit\Framework\TestCase;

/** @internal */
final class PimcoreConfigurator
{
    private static ?\Closure $bootKernel = null;
    private static ?\Closure $shutdownKernel = null;

    /** @var list<PimcoreConfiguration> */
    private static array $configurators = [];

    public static function setUp(?\Closure $bootKernel = null, ?\Closure $shutdownKernel = null): void
    {
        self::$bootKernel = $bootKernel;
        self::$shutdownKernel = $shutdownKernel;
    }

    public static function apply(TestCase $testCase): void
    {
        foreach (self::iterateConfigurators(AttributeProvider::getAttributes($testCase, PimcoreConfiguration::class)) as $configurator) {
            $configurator->apply();

            // Only remember what actually got applied: if `apply()` throws - or the iteration aborts
            // before reaching a configurator - `reset()` must not restore state that was never backed up.
            self::$configurators[] = $configurator;
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
     * @param list<PimcoreConfiguration> $configurators
     */
    public static function iterateConfigurators(array $configurators): \Generator
    {
        $kernel = null;

        try {
            foreach ($configurators as $configurator) {
                if (!$kernel && $configurator::requiresBootedKernel()) {
                    if (!self::$bootKernel) {
                        throw DoesNotExtendKernelTestCase::forAttribute($configurator::class);
                    }

                    $kernel = (self::$bootKernel)();
                }

                yield $configurator;
            }
        } finally {
            // Also runs when the consumer of this generator throws, so a kernel booted here is never leaked.
            if ($kernel && self::$shutdownKernel) {
                (self::$shutdownKernel)();
            }
        }
    }
}
