<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

trait WithoutAdminMode
{
    /** @internal */
    private static bool $adminModeWasEnabled;

    /**
     * @internal
     *
     * @beforeClass
     */
    public static function _disableAdminMode(): void
    {
        self::$adminModeWasEnabled = AdminMode::isEnabled();
        AdminMode::disable();
    }

    /**
     * @internal
     *
     * @afterClass
     */
    public static function _resetAdminMode(): void
    {
        if (true === self::$adminModeWasEnabled) {
            AdminMode::enable();
        }
    }
}
