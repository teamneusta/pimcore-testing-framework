<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Pimcore\Bootstrap;
use Symfony\Component\Dotenv\Dotenv;

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
        self::loadDotEnv();
        Bootstrap::bootstrap();
        AdminMode::disable();
    }

    public static function setEnv(string $name, string $value): void
    {
        putenv("{$name}=" . $_ENV[$name] = $_SERVER[$name] = $value);
    }

    private static function loadDotEnv(): void
    {
        if (isset($_SERVER['SYMFONY_DOTENV_VARS'])) {
            return;
        }

        if (!class_exists(Dotenv::class)) {
            return;
        }

        if (file_exists($filename = PIMCORE_PROJECT_ROOT . '/.env')) {
            (new Dotenv())->bootEnv($filename);
        }
    }
}
