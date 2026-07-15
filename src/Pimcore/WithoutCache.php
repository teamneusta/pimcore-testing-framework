<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Pimcore\Cache;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @mixin KernelTestCase
 */
trait WithoutCache
{
    protected static function bootKernel(array $options = []): KernelInterface
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
