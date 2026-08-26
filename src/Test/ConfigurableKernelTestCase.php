<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test;

use Neusta\Pimcore\TestingFramework\ConfigurableKernel;
use Pimcore\Test\KernelTestCase;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.15',
    'The "%s" class is deprecated, use the "%s" trait instead.',
    ConfigurableKernelTestCase::class,
    ConfigurableKernel::class,
);

/**
 * @deprecated since 0.15, use Neusta\Pimcore\TestingFramework\ConfigurableKernel instead
 */
abstract class ConfigurableKernelTestCase extends KernelTestCase
{
    use ConfigurableKernel;
}
