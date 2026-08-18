<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Attribute\Pimcore;

use Neusta\Pimcore\TestingFramework\Pimcore\AdminMode as AdminModeHelper;
use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class AdminMode implements PimcoreConfiguration
{
    private bool $wasEnabled;

    public static function requiresBootedKernel(): bool
    {
        return false;
    }

    public function __construct(
        private readonly bool $enable = true,
    ) {
    }

    public function apply(): void
    {
        $this->wasEnabled = AdminModeHelper::isEnabled();

        self::toggle($this->enable);
    }

    public function reset(): void
    {
        self::toggle($this->wasEnabled);
    }

    private static function toggle(bool $enable): void
    {
        if ($enable) {
            AdminModeHelper::enable();
        } else {
            AdminModeHelper::disable();
        }
    }
}
