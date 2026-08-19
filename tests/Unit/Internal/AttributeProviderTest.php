<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Internal;

use Neusta\Pimcore\TestingFramework\Internal\AttributeProvider;
use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\ConfiguredTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\InheritedTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\RecordingConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AttributeProviderTest extends TestCase
{
    /**
     * @param list<PimcoreConfiguration> $attributes
     *
     * @return list<string>
     */
    private static function ids(array $attributes): array
    {
        return array_map(
            static function (PimcoreConfiguration $attribute): string {
                $attribute->apply();
                $ids = RecordingConfiguration::$applied;
                RecordingConfiguration::forget();

                return end($ids) ?: '?';
            },
            $attributes,
        );
    }

    protected function tearDown(): void
    {
        RecordingConfiguration::forget();
    }

    /** @test */
    #[Test]
    public function it_collects_attributes_from_the_whole_class_hierarchy_parents_first(): void
    {
        $attributes = AttributeProvider::getAttributes(
            new InheritedTestCase('inherited'),
            PimcoreConfiguration::class,
        );

        self::assertSame(['parent', 'child'], self::ids($attributes));
    }

    /** @test */
    #[Test]
    public function method_attributes_come_after_the_class_hierarchy(): void
    {
        $attributes = AttributeProvider::getAttributes(
            new InheritedTestCase('inherited_with_method_attribute'),
            PimcoreConfiguration::class,
        );

        self::assertSame(['parent', 'child', 'method'], self::ids($attributes));
    }

    /**
     * The walk stops at `TOPMOST_TEST_CASES`, so PHPUnit's own base classes are never reflected over.
     *
     * @test
     */
    #[Test]
    public function it_stops_walking_at_the_phpunit_base_class(): void
    {
        $attributes = AttributeProvider::getAttributes(
            new ConfiguredTestCase('only_class_level'),
            PimcoreConfiguration::class,
        );

        self::assertSame(['class'], self::ids($attributes));
    }

    /**
     * Class attributes are cached per test class, because PHPUnit re-instantiates the test case for
     * every method and data-provider row. The cache must not swallow method-level attributes.
     *
     * @test
     */
    #[Test]
    public function the_per_class_cache_does_not_leak_between_methods_of_the_same_class(): void
    {
        $withoutMethodAttribute = AttributeProvider::getAttributes(
            new ConfiguredTestCase('only_class_level'),
            PimcoreConfiguration::class,
        );
        $withMethodAttribute = AttributeProvider::getAttributes(
            new ConfiguredTestCase('class_and_method_level'),
            PimcoreConfiguration::class,
        );

        self::assertSame(['class'], self::ids($withoutMethodAttribute));
        self::assertSame(['class', 'method'], self::ids($withMethodAttribute));
    }

    /**
     * The class hierarchy walk is cached per test class - PHPUnit re-instantiates the test case for
     * every method and data-provider row, and re-walking parents each time would be wasteful. But only
     * the reflection is cached, never the attribute instances: a `PimcoreConfiguration` keeps its state
     * backup on itself, so sharing one instance across test methods would let one test's backup leak
     * into another's - the bug `StackedAttributeTest` guards against.
     *
     * @test
     */
    #[Test]
    public function class_level_attributes_are_fresh_instances_on_every_call(): void
    {
        $first = AttributeProvider::getAttributes(new ConfiguredTestCase('only_class_level'), PimcoreConfiguration::class);
        $second = AttributeProvider::getAttributes(new ConfiguredTestCase('only_class_level'), PimcoreConfiguration::class);

        self::assertCount(1, $first);
        self::assertCount(1, $second);
        self::assertEquals($first[0], $second[0], 'still the same attribute, constructed with the same arguments');
        self::assertNotSame($first[0], $second[0], 'but never the same instance');
    }
}
