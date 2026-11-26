<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration;

use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;
use Pimcore\Cache\RuntimeCache;

/**
 * Stands in for a configuration that creates fixtures: loading or saving elements puts them into Pimcore's
 * process-wide runtime cache, where they must not survive into the test or the following tests.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class WritesToRuntimeCache implements PimcoreConfiguration
{
    public const APPLIED = 'pimcore_testing_framework_written_on_apply';
    public const RESET = 'pimcore_testing_framework_written_on_reset';

    public static function requiresBootedKernel(): bool
    {
        return true;
    }

    public function apply(): void
    {
        RuntimeCache::set(self::APPLIED, true);
    }

    public function reset(): void
    {
        RuntimeCache::set(self::RESET, true);
    }
}
