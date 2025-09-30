<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test;

use Neusta\Pimcore\TestingFramework\ConfigurableKernel;
use Pimcore\Test\WebTestCase;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" class is deprecated, use the "%s" trait instead.',
    ConfigurableWebTestcase::class,
    ConfigurableKernel::class,
);

/**
 * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\ConfigurableKernel instead
 */
abstract class ConfigurableWebTestcase extends WebTestCase
{
    use ConfigurableKernel;
}
