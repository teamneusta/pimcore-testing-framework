<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\AdminMode;
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\DataObjectInheritance;
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\Versioning;
use Neusta\Pimcore\TestingFramework\ConfigurablePimcore;
use Neusta\Pimcore\TestingFramework\Pimcore\AdminMode as AdminModeHelper;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Model\DataObject;
use Pimcore\Model\Version;
use Pimcore\Test\KernelTestCase;

#[AdminMode(true)]
final class ConfigurablePimcoreTest extends KernelTestCase
{
    use ConfigurablePimcore;

    /** @test */
    #[Test]
    public function a_class_level_attribute_applies_to_every_test(): void
    {
        self::assertTrue(AdminModeHelper::isEnabled());
    }

    /** @test */
    #[Test]
    #[AdminMode(false)]
    public function a_method_level_attribute_wins_over_the_class_level_one(): void
    {
        self::assertFalse(AdminModeHelper::isEnabled());
    }

    /**
     * `BootstrapPimcore::bootstrap()` turns versioning off, so this proves the attribute - not the
     * default - is what flipped it.
     *
     * @test
     */
    #[Test]
    #[Versioning(true)]
    public function it_enables_versioning_for_a_single_test(): void
    {
        self::assertTrue(Version::isEnabled());
    }

    /** @test */
    #[Test]
    #[DataObjectInheritance(false)]
    public function it_applies_attributes_without_needing_a_kernel(): void
    {
        self::assertFalse(DataObject::getGetInheritedValues());
    }
}
