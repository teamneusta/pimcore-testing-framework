<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\Cache as CacheAttribute;
use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Pimcore\Cache;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" trait is deprecated, use the "#[%s(enable: true)]" attribute instead.',
    WithoutCache::class,
    CacheAttribute::class,
);

/**
 * @mixin KernelTestCase
 *
 * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\Cache instead
 */
trait WithoutCache
{
    protected static function bootKernel(array $options = [])
    {
        if (!is_subclass_of(static::class, KernelTestCase::class)) {
            throw DoesNotExtendKernelTestCase::forTrait(__TRAIT__);
        }

        try {
            return parent::bootKernel($options);
        } finally {
            Cache::disable();
        }
    }
}
