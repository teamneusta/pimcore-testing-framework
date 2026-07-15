<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Fixtures\NotAKernelTestCase;

use Neusta\Pimcore\TestingFramework\Pimcore\WithoutCache;

final class UsingWithoutCache
{
    use WithoutCache;

    public static function boot(array $options = []): mixed
    {
        return self::bootKernel($options);
    }
}
