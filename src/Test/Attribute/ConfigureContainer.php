<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureContainer as NewConfigureContainer;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" attribute is deprecated, use "%s" instead.',
    ConfigureContainer::class,
    NewConfigureContainer::class,
);

class_alias(NewConfigureContainer::class, ConfigureContainer::class);

if (false) {
    /**
     * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureContainer instead
     */
    #[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
    class ConfigureContainer
    {
    }
}
