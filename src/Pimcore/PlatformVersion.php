<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Composer\InstalledVersions;

final class PlatformVersion
{
    public static function getMajor(): int
    {
        $version = InstalledVersions::getVersion('pimcore/pimcore');

        if (null === $version) {
            throw new \RuntimeException('Could not determine the installed version of "pimcore/pimcore".');
        }

        return (int) strtok($version, '.');
    }
}
