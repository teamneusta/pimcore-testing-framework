<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureRoute as NewConfigureRoute;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.15',
    'The "%s" attribute is deprecated, use "%s" instead.',
    ConfigureRoute::class,
    NewConfigureRoute::class,
);

class_alias(NewConfigureRoute::class, ConfigureRoute::class);

if (false) {
    /**
     * @deprecated since 0.15, use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureRoute instead
     */
    #[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
    class ConfigureRoute
    {
    }
}
