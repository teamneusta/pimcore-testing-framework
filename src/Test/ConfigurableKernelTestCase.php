<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test;

use Neusta\Pimcore\TestingFramework\KernelTestCase;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" class is deprecated, use "%s" instead.',
    ConfigurableKernelTestCase::class,
    KernelTestCase::class,
);

class_alias(KernelTestCase::class, ConfigurableKernelTestCase::class);

if (false) {
    /**
     * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\KernelTestCase instead
     */
    abstract class ConfigurableKernelTestCase extends KernelTestCase
    {
    }
}
