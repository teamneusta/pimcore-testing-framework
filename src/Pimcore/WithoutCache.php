<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Pimcore\Cache;
use Pimcore\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
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
