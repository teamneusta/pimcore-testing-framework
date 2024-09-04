<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Pimcore\AdminMode;
use Pimcore\Bootstrap;
use Pimcore\Model\Version;

final class BootstrapPimcore
{
    private const DEFAULT_ENV_VARS = [
        'APP_ENV' => 'test',
    ];

    public static function bootstrap(string ...$envVars): void
    {
        foreach ($envVars + self::DEFAULT_ENV_VARS as $name => $value) {
            self::setEnv($name, $value);
        }

        Bootstrap::setProjectRoot();
        Bootstrap::bootstrap();
        AdminMode::disable();
        Version::disable();
    }

    public static function setEnv(string $name, string $value): void
    {
        putenv("{$name}=" . $_ENV[$name] = $_SERVER[$name] = $value);
    }
}
