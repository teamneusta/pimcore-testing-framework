<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureExtension as NewConfigureExtension;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" attribute is deprecated, use "%s" instead.',
    ConfigureExtension::class,
    NewConfigureExtension::class,
);

class_alias(NewConfigureExtension::class, ConfigureExtension::class);

if (false) {
    /**
     * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureExtension instead
     */
    #[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
    class ConfigureExtension
    {
    }
}
