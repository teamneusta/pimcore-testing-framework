<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Internal;

use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\DataObjectInheritance;
use Neusta\Pimcore\TestingFramework\Internal\AttributeProvider;
use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\ConfiguredTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\InheritedTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration\RecordingConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject;

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

    protected function setUp(): void
    {
        RecordingConfiguration::forget();
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
     * Class-level attributes are instantiated once per test class and then reused for every method and
     * data-provider row, while method-level ones are rebuilt on each call. That is safe for the
     * `ConfigurePimcore` attributes only because `apply()` re-reads the current state every time - it is
     * what makes a shared instance survive repeated apply/reset cycles.
     *
     * @test
     */
    #[Test]
    public function class_attributes_are_cached_per_class_and_survive_repeated_apply_reset_cycles(): void
    {
        $first = AttributeProvider::getAttributes(new ConfiguredTestCase('only_class_level'), PimcoreConfiguration::class);
        $second = AttributeProvider::getAttributes(new ConfiguredTestCase('only_class_level'), PimcoreConfiguration::class);

        self::assertSame($first[0], $second[0], 'class attributes are expected to be cached per test class');

        $original = DataObject::getGetInheritedValues();

        try {
            $shared = new DataObjectInheritance(!$original);

            // Two sequential test methods sharing one cached attribute instance.
            $shared->apply();
            self::assertSame(!$original, DataObject::getGetInheritedValues());
            $shared->reset();
            self::assertSame($original, DataObject::getGetInheritedValues());

            $shared->apply();
            self::assertSame(!$original, DataObject::getGetInheritedValues());
            $shared->reset();
            self::assertSame($original, DataObject::getGetInheritedValues(), 'the second cycle must restore the original state too');
        } finally {
            DataObject::setGetInheritedValues($original);
        }
    }
}
