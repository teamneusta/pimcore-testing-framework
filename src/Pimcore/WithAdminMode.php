<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\AdminMode as AdminModeAttribute;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.15',
    'The "%s" trait is deprecated, use the "#[%s(enable: true)]" attribute instead.',
    WithAdminMode::class,
    AdminModeAttribute::class,
);

/**
 * @deprecated since 0.15, use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\AdminMode instead
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
