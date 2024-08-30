<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\Attribute\Kernel\RegisterCompilerPass as NewRegisterCompilerPass;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" attribute is deprecated, use "%s" instead.',
    RegisterCompilerPass::class,
    NewRegisterCompilerPass::class,
);

class_alias(NewRegisterCompilerPass::class, RegisterCompilerPass::class);

if (false) {
    /**
     * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\Attribute\Kernel\RegisterCompilerPass instead
     */
    #[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
    class RegisterCompilerPass
    {
    }
}
