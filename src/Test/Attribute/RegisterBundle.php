<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\Attribute\Kernel\RegisterBundle as NewRegisterBundle;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" attribute is deprecated, use "%s" instead.',
    RegisterBundle::class,
    NewRegisterBundle::class,
);

class_alias(NewRegisterBundle::class, RegisterBundle::class);

if (false) {
    /**
     * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\Attribute\Kernel\RegisterBundle instead
     */
    #[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
    class RegisterBundle
    {
    }
}
