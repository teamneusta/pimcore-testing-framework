<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\KernelConfiguration as RootKernelConfiguration;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.15',
    'The "%s" interface is deprecated, use "%s" instead.',
    KernelConfiguration::class,
    RootKernelConfiguration::class,
);

class_alias(RootKernelConfiguration::class, KernelConfiguration::class);

if (false) {
    /**
     * @deprecated since 0.15, use Neusta\Pimcore\TestingFramework\KernelConfiguration instead
     */
    interface KernelConfiguration extends RootKernelConfiguration
    {
    }
}
