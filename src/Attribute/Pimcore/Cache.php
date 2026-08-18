<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Attribute\Pimcore;

use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class Cache implements PimcoreConfiguration
{
    private bool $wasEnabled;

    public static function requiresBootedKernel(): bool
    {
        return true;
    }

    public function __construct(
        private readonly bool $enable = true,
    ) {
    }

    public function apply(): void
    {
        $this->wasEnabled = \Pimcore\Cache::isEnabled();

        self::toggle($this->enable);
    }

    public function reset(): void
    {
        self::toggle($this->wasEnabled);
    }

    private static function toggle(bool $enable): void
    {
        if ($enable) {
            \Pimcore\Cache::enable();
        } else {
            \Pimcore\Cache::disable();
        }
    }
}
