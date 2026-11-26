<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\ConfigurablePimcore;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\WritesToRuntimeCache;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Test\KernelTestCase;

/**
 * Pimcore's runtime cache is process-wide; what a configuration that requires a booted kernel writes into it is
 * cleared when its kernel shuts down (`LongRunningHelper::cleanUp()`, except for a few protected keys), right after
 * `apply()` and again after `reset()`.
 *
 * The tests depend on running in the order they are declared.
 */
final class KernelRequiringPimcoreConfigurationStateTest extends KernelTestCase
{
    use ConfigurablePimcore;

    /** @test */
    #[Test]
    #[WritesToRuntimeCache]
    public function what_the_configuration_caches_while_applying_does_not_reach_the_test(): void
    {
        self::assertFalse(RuntimeCache::isRegistered(WritesToRuntimeCache::APPLIED));
    }

    /** @test */
    #[Test]
    public function what_the_configuration_caches_while_resetting_does_not_reach_the_following_test(): void
    {
        self::assertFalse(RuntimeCache::isRegistered(WritesToRuntimeCache::APPLIED));
        self::assertFalse(RuntimeCache::isRegistered(WritesToRuntimeCache::RESET));
    }
}
