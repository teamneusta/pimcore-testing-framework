<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Pimcore;

use Neusta\Pimcore\TestingFramework\Pimcore\PlatformVersion;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PlatformVersionTest extends TestCase
{
    /** @test */
    #[Test]
    public function it_reads_a_supported_major_version_from_the_installed_pimcore_pimcore_package(): void
    {
        // Whichever of these is actually installed depends on the CI matrix row this runs in.
        self::assertContains(PlatformVersion::getMajor(), [11, 12, 2026]);
    }
}
