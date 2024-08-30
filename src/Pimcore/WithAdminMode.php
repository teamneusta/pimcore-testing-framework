<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\AdminMode as AdminModeAttribute;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" trait is deprecated, use the "#[%s]" attribute instead.',
    WithAdminMode::class,
    AdminModeAttribute::class,
);

/**
 * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\AdminMode instead
 */
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
