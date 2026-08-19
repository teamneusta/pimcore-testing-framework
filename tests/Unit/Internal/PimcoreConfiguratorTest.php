<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Internal;

use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Neusta\Pimcore\TestingFramework\Internal\PimcoreConfigurator;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\ConfiguredTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\RecordingConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PimcoreConfiguratorTest extends TestCase
{
    protected function tearDown(): void
    {
        PimcoreConfigurator::reset();
        RecordingConfiguration::forget();
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
        $boots = $shutdowns = 0;

        PimcoreConfigurator::useKernel(
            static function () use (&$boots) {
                ++$boots;

                return new \stdClass();
            },
            static function () use (&$shutdowns) { ++$shutdowns; },
        );

        PimcoreConfigurator::collect(new ConfiguredTestCase('class_and_method_level'));
        PimcoreConfigurator::apply();

        self::assertSame(1, $boots, 'two configurators requiring a kernel should still boot it only once');
        self::assertSame(1, $shutdowns);
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
        $shutdowns = 0;

        PimcoreConfigurator::useKernel(
            static fn () => new \stdClass(),
            static function () use (&$shutdowns) { ++$shutdowns; },
        );

        try {
            PimcoreConfigurator::collect(new ConfiguredTestCase('method_level_fails'));
            PimcoreConfigurator::apply();
        } catch (\RuntimeException) {
            // expected
        }

        self::assertSame(1, $shutdowns);
    }
}
