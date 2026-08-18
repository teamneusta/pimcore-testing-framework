<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Attribute\Kernel\RegisterBundle;
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\AdminMode;
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\Cache as CacheAttribute;
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\DataObjectInheritance;
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\Versioning;
use Neusta\Pimcore\TestingFramework\ConfigurableKernel;
use Neusta\Pimcore\TestingFramework\ConfigurablePimcore;
use Neusta\Pimcore\TestingFramework\Pimcore\AdminMode as AdminModeHelper;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Cache;
use Pimcore\Model\DataObject;
use Pimcore\Model\Version;
use Pimcore\Test\KernelTestCase;

#[AdminMode(true)]
final class ConfigurablePimcoreTest extends KernelTestCase
{
    use ConfigurableKernel;
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

    /**
     * `Cache` is the only attribute whose `requiresBootedKernel()` is true, so this is the path where
     * `PimcoreConfigurator` boots a kernel of its own before applying.
     *
     * @test
     */
    #[Test]
    #[CacheAttribute(false)]
    public function it_boots_a_kernel_for_attributes_that_need_one(): void
    {
        self::assertFalse(Cache::isEnabled());
    }

    /**
     * Both traits contribute `@before` hooks; using them together on one test case has to keep working.
     *
     * @test
     */
    #[Test]
    #[RegisterBundle(ConfigurationBundle::class)]
    #[ConfigureExtension('configuration', ['foo' => 'value1', 'bar' => ['value2']])]
    #[CacheAttribute(false)]
    public function it_combines_kernel_and_pimcore_configuration(): void
    {
        self::assertFalse(Cache::isEnabled());
        self::assertSame('value1', self::getContainer()->getParameter('configuration.foo'));
    }
}
