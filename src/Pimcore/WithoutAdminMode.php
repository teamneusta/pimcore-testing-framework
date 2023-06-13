<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

trait WithoutAdminMode
{
    /**
     * @internal
     *
     * @beforeClass
     */
    public static function _disableAdminMode(): void
    {
        AdminMode::disable();
    }
}
