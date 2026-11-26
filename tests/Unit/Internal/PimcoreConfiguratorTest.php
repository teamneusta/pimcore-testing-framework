<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Internal;

use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Neusta\Pimcore\TestingFramework\Internal\PimcoreConfigurator;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\ConfiguredTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\RecordingConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class PimcoreConfiguratorTest extends TestCase
{
    private ?KernelInterface $pimcoreKernel;

    protected function setUp(): void
    {
        $this->pimcoreKernel = \Pimcore::getKernel();
    }

    protected function tearDown(): void
    {
        // Forget first, so resetting what a test left applied no longer needs a kernel.
        RecordingConfiguration::forget();
        PimcoreConfigurator::useKernel();
        PimcoreConfigurator::reset();

        // `Pimcore::setKernel()` does not accept null, and a test may have replaced it with a mock.
        (new \ReflectionProperty(\Pimcore::class, 'kernel'))->setValue(null, $this->pimcoreKernel);
    }

    /** @test */
    #[Test]
    public function it_applies_class_attributes_before_method_attributes_and_resets_in_reverse(): void
    {
        PimcoreConfigurator::useKernel();

        PimcoreConfigurator::collect(new ConfiguredTestCase('class_and_method_level'));
        PimcoreConfigurator::apply();

        self::assertSame(['class', 'method'], RecordingConfiguration::$applied);

        PimcoreConfigurator::reset();

        self::assertSame(['method', 'class'], RecordingConfiguration::$reset);
    }

    /**
     * Regression: `apply()` used to store every collected configurator up front, so a failure midway
     * left `reset()` walking configurators that had never backed anything up - masking the real error
     * behind a "typed property must not be accessed before initialization".
     *
     * @test
     */
    #[Test]
    public function reset_skips_configurators_whose_apply_never_completed(): void
    {
        PimcoreConfigurator::useKernel();

        try {
            PimcoreConfigurator::collect(new ConfiguredTestCase('method_level_fails'));
            PimcoreConfigurator::apply();
            self::fail('apply() should have propagated the failure of the method-level configurator');
        } catch (\RuntimeException $e) {
            self::assertSame('apply() failed for method', $e->getMessage());
        }

        self::assertSame(['class'], RecordingConfiguration::$applied);

        PimcoreConfigurator::reset();

        self::assertSame(['class'], RecordingConfiguration::$reset, 'the failed configurator must not be reset');
    }

    /** @test */
    #[Test]
    public function it_reports_the_attribute_that_needs_a_kernel_when_none_can_be_booted(): void
    {
        RecordingConfiguration::needsKernel(true);
        // No boot closure: this is what `ConfigurablePimcore` does on a plain TestCase.
        PimcoreConfigurator::useKernel();

        $this->expectException(DoesNotExtendKernelTestCase::class);
        $this->expectExceptionMessage(RecordingConfiguration::class);

        PimcoreConfigurator::collect(new ConfiguredTestCase('only_class_level'));
        PimcoreConfigurator::apply();
    }

    /** @test */
    #[Test]
    public function it_boots_a_kernel_once_and_shuts_it_down_afterwards(): void
    {
        RecordingConfiguration::needsKernel(true);
        $creations = 0;
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects(self::once())->method('boot');
        $kernel->expects(self::once())->method('shutdown');

        PimcoreConfigurator::useKernel(static function () use (&$creations, $kernel) {
            ++$creations;

            return $kernel;
        });

        PimcoreConfigurator::collect(new ConfiguredTestCase('class_and_method_level'));
        PimcoreConfigurator::apply();

        self::assertSame(1, $creations, 'two configurators requiring a kernel should still create it only once');
    }

    /**
     * The kernel is booted inside a generator; if its consumer throws, the shutdown used to be skipped.
     *
     * @test
     */
    #[Test]
    public function it_shuts_the_kernel_down_even_when_a_configurator_fails(): void
    {
        RecordingConfiguration::needsKernel(true);
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects(self::once())->method('shutdown');

        PimcoreConfigurator::useKernel(static fn () => $kernel);

        try {
            PimcoreConfigurator::collect(new ConfiguredTestCase('method_level_fails'));
            PimcoreConfigurator::apply();
        } catch (\RuntimeException) {
            // expected
        }
    }

    /**
     * Regression for issue 60: `Pimcore\Test\KernelTestCase::createKernel()` registers every new kernel as
     * Pimcore's static one. Left pointing to the configurator's kernel after its shutdown, Pimcore resolved its
     * container through a dead kernel while the test booted or used another one.
     *
     * @test
     */
    #[Test]
    public function it_points_pimcore_back_to_the_previous_kernel_after_shutting_its_own_down(): void
    {
        RecordingConfiguration::needsKernel(true);
        $previousKernel = $this->createStub(KernelInterface::class);
        $ownKernel = $this->createMock(KernelInterface::class);
        $ownKernel->expects(self::once())->method('shutdown')->willReturnCallback(
            // Pimcore's `Kernel::shutdown()` needs its static kernel to still be the one shutting down.
            static fn () => self::assertSame($ownKernel, \Pimcore::getKernel()),
        );

        \Pimcore::setKernel($previousKernel);
        PimcoreConfigurator::useKernel(static function () use ($ownKernel) {
            \Pimcore::setKernel($ownKernel);

            return $ownKernel;
        });

        PimcoreConfigurator::collect(new ConfiguredTestCase('only_class_level'));
        PimcoreConfigurator::apply();

        self::assertSame($previousKernel, \Pimcore::getKernel());
    }
}
