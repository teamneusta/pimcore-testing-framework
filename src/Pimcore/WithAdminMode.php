<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

trait WithAdminMode
{
    /** @internal */
    private static bool $adminModeWasEnabled;

    /**
     * @internal
     *
     * @beforeClass
     */
    public static function _enableAdminMode(): void
    {
        self::$adminModeWasEnabled = AdminMode::isEnabled();
        AdminMode::enable();
    }

    /**
     * @internal
     *
     * @afterClass
     */
    public static function _resetAdminMode(): void
    {
        if (false === self::$adminModeWasEnabled) {
            AdminMode::disable();
        }
    }
}
