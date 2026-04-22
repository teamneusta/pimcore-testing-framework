<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;

trait WithAdminMode
{
    /** @internal */
    private static bool $adminModeWasEnabled;

    /**
     * @internal
     *
     * @beforeClass
     */
    #[BeforeClass]
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
    #[AfterClass]
    public static function _resetAdminMode(): void
    {
        if (false === self::$adminModeWasEnabled) {
            AdminMode::disable();
        }
    }
}
