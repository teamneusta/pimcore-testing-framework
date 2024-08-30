<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Neusta\Pimcore\TestingFramework\BootstrapPimcore as RootBootstrapPimcore;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" class is deprecated, use "%s" instead.',
    BootstrapPimcore::class,
    RootBootstrapPimcore::class,
);

class_alias(RootBootstrapPimcore::class, BootstrapPimcore::class);

if (false) {
    /**
     * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\BootstrapPimcore instead
     */
    final class BootstrapPimcore
    {
        public static function bootstrap(string ...$envVars): void
        {
        }

        public static function setEnv(string $name, string $value): void
        {
        }
    }
}
