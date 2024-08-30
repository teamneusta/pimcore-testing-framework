<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\Attribute\ConfigureKernel;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" interface is deprecated, use "%s" instead.',
    KernelConfiguration::class,
    ConfigureKernel::class,
);

class_alias(ConfigureKernel::class, KernelConfiguration::class);

if (false) {
    /**
     * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\Attribute\ConfigureKernel instead
     */
    interface KernelConfiguration extends ConfigureKernel
    {
    }
}
