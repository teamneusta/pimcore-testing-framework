<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use Neusta\Pimcore\TestingFramework\ResetDatabase as RootResetDatabase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.15',
    'The "%s" trait is deprecated, use "%s" instead.',
    ResetDatabase::class,
    RootResetDatabase::class,
);

/**
 * @mixin KernelTestCase
 *
 * @deprecated since 0.15, use Neusta\Pimcore\TestingFramework\ResetDatabase instead
 */
trait ResetDatabase
{
    use RootResetDatabase;
}
