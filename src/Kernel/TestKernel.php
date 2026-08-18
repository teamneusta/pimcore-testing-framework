<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Kernel;

use Neusta\Pimcore\TestingFramework\TestKernel as RootTestKernel;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.15',
    'The "%s" class is deprecated, use "%s" instead.',
    TestKernel::class,
    RootTestKernel::class,
);

class_alias(RootTestKernel::class, TestKernel::class);

if (false) {
    /**
     * @deprecated since 0.15, use Neusta\Pimcore\TestingFramework\TestKernel instead
     */
    class TestKernel extends RootTestKernel
    {
    }
}
