<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Pimcore\WithoutCache;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Cache;

final class WithoutCacheTest extends ConfigurableKernelTestCase
{
    use WithoutCache;

    protected function tearDown(): void
    {
        Cache::enable();

        parent::tearDown();
    }

    /** @test */
    #[Test]
    public function it_disables_the_cache_after_booting_the_kernel(): void
    {
        self::bootKernel();
        Cache::enable();
        self::assertTrue(Cache::isEnabled());

        self::bootKernel();
        self::assertFalse(Cache::isEnabled());
    }
}
