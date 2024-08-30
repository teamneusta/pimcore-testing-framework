<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Attribute\Pimcore;

use Neusta\Pimcore\TestingFramework\Attribute\ConfigurePimcore;
use Neusta\Pimcore\TestingFramework\Pimcore\AdminMode as AdminModeHelper;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class AdminMode implements ConfigurePimcore
{
    private static bool $wasEnabled;

    public static function requiresBootedKernel(): bool
    {
        return false;
    }

    public function __construct(
        private readonly bool $enable,
    ) {
    }

    public function apply(): void
    {
        self::$wasEnabled = AdminModeHelper::isEnabled();

        self::toggle($this->enable);
    }

    public function reset(): void
    {
        self::toggle(self::$wasEnabled);
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
