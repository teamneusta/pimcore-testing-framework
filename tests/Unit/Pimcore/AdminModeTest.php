<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Pimcore;

use Neusta\Pimcore\TestingFramework\Pimcore\AdminMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\Document;

final class AdminModeTest extends TestCase
{
    protected function tearDown(): void
    {
        AdminMode::disable();
    }

    /**
     * @test
     */
    public function it_is_disabled_by_default(): void
    {
        self::assertFalse(AdminMode::isEnabled());
    }

    /** @test */
    #[Test]
    public function enable_switches_all_related_statics_together(): void
    {
        AdminMode::enable();

        self::assertTrue(AdminMode::isEnabled());
        self::assertFalse(Document::doHideUnpublished());
        self::assertFalse(DataObject::getHideUnpublished());
        self::assertFalse(DataObject::getGetInheritedValues());
        self::assertFalse(Localizedfield::getGetFallbackValues());
    }

    /** @test */
    #[Test]
    public function disable_switches_all_related_statics_together(): void
    {
        AdminMode::enable();

        AdminMode::disable();

        self::assertFalse(AdminMode::isEnabled());
        self::assertTrue(Document::doHideUnpublished());
        self::assertTrue(DataObject::getHideUnpublished());
        self::assertTrue(DataObject::getGetInheritedValues());
        self::assertTrue(Localizedfield::getGetFallbackValues());
    }
}
