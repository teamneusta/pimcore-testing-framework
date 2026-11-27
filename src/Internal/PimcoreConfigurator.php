<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Internal;

use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/** @internal */
final class PimcoreConfigurator
{
    /** @var (\Closure(): KernelInterface)|null */
    private static ?\Closure $createKernel = null;

    /** @var list<PimcoreConfiguration> */
    private static array $collected = [];
    /** @var list<PimcoreConfiguration> */
    private static array $applied = [];

    /**
     * @param (\Closure(): KernelInterface)|null $createKernel creates a new, not yet booted kernel
     */
    public static function useKernel(?\Closure $createKernel = null): void
    {
        self::$createKernel = $createKernel;
    }

    public static function collect(TestCase $testCase): void
    {
        self::$collected = AttributeProvider::getAttributes($testCase, PimcoreConfiguration::class);
    }

    public static function apply(): void
    {
        foreach (self::iterateConfigurators(self::$collected) as $configurator) {
            $configurator->apply();

            // Only remember what actually got applied: if `apply()` throws - or the iteration aborts
            // before reaching a configurator - `reset()` must not restore state that was never backed up.
            self::$applied[] = $configurator;
        }
    }

    public static function reset(): void
    {
        foreach (self::iterateConfigurators(array_reverse(self::$applied)) as $configurator) {
            $configurator->reset();
        }

        self::$collected = [];
        self::$applied = [];
    }

    /**
     * Configurators that require a booted kernel get an isolated one, not the test case's (`bootKernel()`): that
     * would shut down a kernel the test may still use, and leave a shut-down kernel in `KernelTestCase::$kernel`,
     * which the next `bootKernel()` re-boots just to shut it down again (see issue 60).
     *
     * @param list<PimcoreConfiguration> $configurators
     */
    private static function iterateConfigurators(array $configurators): \Generator
    {
        $kernel = null;

        try {
            foreach ($configurators as $configurator) {
                if (!$kernel && $configurator::requiresBootedKernel()) {
                    if (!self::$createKernel) {
                        throw DoesNotExtendKernelTestCase::forAttribute($configurator::class);
                    }

                    $kernel = IsolatedKernel::boot(self::$createKernel);
                }

                yield $configurator;
            }
        } finally {
            // Also runs when the consumer of this generator throws, so a kernel booted here is never leaked.
            $kernel?->shutdown();
        }
    }
}
