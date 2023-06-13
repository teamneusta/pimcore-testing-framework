<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Pimcore\Bootstrap;

final class BootstrapPimcore
{
    public static function bootstrap(string $env = 'test'): void
    {
        self::setEnv('APP_ENV', $env);

        Bootstrap::setProjectRoot();
        Bootstrap::bootstrap();
    }

    /**
     * @param mixed $value
     */
    public static function setEnv(string $name, $value): void
    {
        putenv("{$name}=".$_ENV[$name] = $_SERVER[$name] = $value);
    }
}
