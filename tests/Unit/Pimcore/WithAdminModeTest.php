<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Pimcore;

use Neusta\Pimcore\TestingFramework\Pimcore\AdminMode;
use Neusta\Pimcore\TestingFramework\Pimcore\WithAdminMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WithAdminModeTest extends TestCase
{
    use WithAdminMode;

    protected function tearDown(): void
    {
        // Safety net: guarantee no admin-mode state leaks into other test classes,
        // regardless of what _resetAdminMode()'s restore logic below decides.
        AdminMode::disable();
    }

    /** @test */
    #[Test]
    public function it_enables_admin_mode(): void
    {
        self::assertTrue(AdminMode::isEnabled());
    }

    /** @test */
    #[Test]
    public function it_does_not_disable_admin_mode_that_was_already_enabled_before_it_ran(): void
    {
        // Simulate admin mode having been enabled by something other than this trait.
        AdminMode::enable();

        self::_enableAdminMode();
        self::_resetAdminMode();

        self::assertTrue(AdminMode::isEnabled());
    }

    /** @test */
    #[Test]
    public function it_disables_admin_mode_it_enabled_itself(): void
    {
        AdminMode::disable();

        self::_enableAdminMode();
        self::assertTrue(AdminMode::isEnabled());

        self::_resetAdminMode();

        self::assertFalse(AdminMode::isEnabled());
    }
}
