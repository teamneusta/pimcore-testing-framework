<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

trait WithAdminMode
{
    /**
     * @internal
     *
     * @beforeClass
     */
    public static function _enableAdminMode(): void
    {
        AdminMode::enable();
    }
}
