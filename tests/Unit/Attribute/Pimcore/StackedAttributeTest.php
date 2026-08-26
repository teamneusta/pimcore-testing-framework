<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Attribute\Pimcore;

use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\AdminMode as AdminModeAttribute;
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\DataObjectInheritance;
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\RuntimeCache as RuntimeCacheAttribute;
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\Versioning;
use Neusta\Pimcore\TestingFramework\Pimcore\AdminMode;
use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\DataObject;
use Pimcore\Model\Version;

/**
 * A `ConfigurePimcore` attribute may legitimately appear on the class *and* on the test method -
 * `AttributeProvider` concatenates both, and `PimcoreConfigurator` applies them in order and resets
 * them in reverse. That only restores the original state if each attribute instance keeps its own
 * backup; a backup shared across instances gets overwritten by the second `apply()`.
 */
final class StackedAttributeTest extends TestCase
{
    /** @var list<callable():void> */
    private array $restore = [];

    protected function tearDown(): void
    {
        foreach ($this->restore as $restore) {
            $restore();
        }

        $this->restore = [];
    }

    /**
     * @param class-string<PimcoreConfiguration> $attribute
     * @param callable():bool                    $read
     * @param callable(bool):void                $write
     */
    /**
     * @test
     *
     * @dataProvider provideKernelLessAttributes
     */
    #[Test]
    #[DataProvider('provideKernelLessAttributes')]
    public function stacked_attributes_restore_the_original_state(string $attribute, callable $read, callable $write): void
    {
        $before = $read();
        $this->restore[] = static fn () => $write($before);

        foreach ([false, true] as $original) {
            $write($original);

            // What `#[Attr(!$original)]` on the class plus `#[Attr($original)]` on the method produces.
            $outer = new $attribute(!$original);
            $inner = new $attribute($original);

            $outer->apply();
            self::assertSame(!$original, $read(), 'the class-level attribute should have been applied');

            $inner->apply();
            self::assertSame($original, $read(), 'the method-level attribute should win while the test runs');

            // `PimcoreConfigurator::reset()` walks the configurators in reverse.
            $inner->reset();
            self::assertSame(!$original, $read(), 'resetting the method-level attribute must restore what the class-level one set');

            $outer->reset();
            self::assertSame($original, $read(), 'resetting the class-level attribute must restore the state from before the test');
        }
    }

    /**
     * Only the attributes whose `requiresBootedKernel()` is `false`, so they can be driven without a kernel.
     *
     * @return iterable<string, array{class-string<PimcoreConfiguration>, callable():bool, callable(bool):void}>
     */
    public static function provideKernelLessAttributes(): iterable
    {
        yield 'AdminMode' => [
            AdminModeAttribute::class,
            static fn (): bool => AdminMode::isEnabled(),
            static function (bool $enable): void {
                $enable ? AdminMode::enable() : AdminMode::disable();
            },
        ];

        yield 'DataObjectInheritance' => [
            DataObjectInheritance::class,
            static fn (): bool => DataObject::getGetInheritedValues(),
            static fn (bool $enable) => DataObject::setGetInheritedValues($enable),
        ];

        yield 'RuntimeCache' => [
            RuntimeCacheAttribute::class,
            static fn (): bool => RuntimeCache::isEnabled(),
            static function (bool $enable): void {
                $enable ? RuntimeCache::enable() : RuntimeCache::disable();
            },
        ];

        yield 'Versioning' => [
            Versioning::class,
            static fn (): bool => Version::isEnabled(),
            static function (bool $enable): void {
                $enable ? Version::enable() : Version::disable();
            },
        ];
    }

    /**
     * @param class-string<PimcoreConfiguration> $attribute
     * @param callable():bool                    $read
     * @param callable(bool):void                $write
     */
    /**
     * @test
     *
     * @dataProvider provideKernelLessAttributes
     */
    #[Test]
    #[DataProvider('provideKernelLessAttributes')]
    public function enable_defaults_to_true(string $attribute, callable $read, callable $write): void
    {
        $before = $read();
        $this->restore[] = static fn () => $write($before);

        $write(false);

        // Without a default, the deprecated traits' own migration hint - e.g. "use #[AdminMode]" -
        // would end in an ArgumentCountError.
        (new $attribute())->apply();

        self::assertTrue($read());
    }
}
