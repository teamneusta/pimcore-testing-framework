<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Internal;

use PHPUnit\Framework\TestCase;
use Pimcore\Test\KernelTestCase as PimcoreKernelTestCase;
use Pimcore\Test\WebTestCase as PimcoreWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

/** @internal */
final class AttributeProvider
{
    /**
     * Look no further than these classes when fetching class attributes.
     *
     * @var list<class-string>
     */
    private const TOPMOST_TEST_CASES = [
        TestCase::class,
        SymfonyKernelTestCase::class,
        PimcoreKernelTestCase::class,
        SymfonyWebTestCase::class,
        PimcoreWebTestCase::class,
    ];

    /**
     * Walking the class hierarchy is the expensive part, so it is cached per test class - but only the
     * reflection, never the attribute instances. PHPUnit re-instantiates the test case for every method
     * and every data-provider row, and a {@see PimcoreConfiguration} keeps its state backup on itself,
     * so each test has to get its own instances.
     *
     * Note: `T` isn't available here - a method template only exists within the scope of the method that
     * declares it, and a static property is shared across every call - so `object` (the upper bound every
     * `T` in this class satisfies) is the widest type that still typechecks. {@see getAttributes()} narrows
     * back to the caller's actual `T` at the point where it reads from this cache.
     *
     * @var array<class-string, array<string, list<\ReflectionAttribute<object>>>>
     */
    private static array $classAttributes = [];

    /**
     * @template T of object
     *
     * @param class-string<T> $name
     *
     * @return list<T>
     */
    public static function getAttributes(TestCase $testCase, string $name): array
    {
        $class = new \ReflectionClass($testCase);

        /** @var list<\ReflectionAttribute<T>> $reflected narrows {@see self::$classAttributes} back to T */
        $reflected = [
            ...self::$classAttributes[$testCase::class][$name] ??= self::reflectClassAttributes($class, $name),
            ...self::reflectAttributes($class->getMethod(self::getTestName($testCase)), $name),
        ];

        return [
            ...array_map(static fn (\ReflectionAttribute $attribute) => $attribute->newInstance(), $reflected),
            ...self::extractAttributesFromProvidedData($testCase, $name),
        ];
    }

    /**
     * `getName()` was removed in PHPUnit 10 in favor of `name()`.
     */
    private static function getTestName(TestCase $testCase): string
    {
        return method_exists($testCase, 'getName') ? $testCase->getName(false) : $testCase->name();
    }

    /**
     * `getProvidedData()` was removed in PHPUnit 10 in favor of `providedData()`.
     *
     * @return array<array-key, mixed>
     */
    private static function getProvidedData(TestCase $testCase): array
    {
        return method_exists($testCase, 'getProvidedData') ? $testCase->getProvidedData() : $testCase->providedData();
    }

    /**
     * @template T of object
     *
     * @param \ReflectionClass<TestCase> $class
     * @param class-string<T>            $name
     *
     * @return list<\ReflectionAttribute<T>>
     */
    private static function reflectClassAttributes(\ReflectionClass $class, string $name): array
    {
        $attributes = [self::reflectAttributes($class, $name)];

        while ($class = $class->getParentClass()) {
            if (\in_array($class->getName(), self::TOPMOST_TEST_CASES, true)) {
                break;
            }

            $attributes[] = self::reflectAttributes($class, $name);
        }

        return array_merge(...array_reverse($attributes));
    }

    /**
     * @template T of object
     *
     * @param \ReflectionClass<TestCase>|\ReflectionMethod $source
     * @param class-string<T>                              $name
     *
     * @return list<\ReflectionAttribute<T>>
     */
    private static function reflectAttributes(\ReflectionClass|\ReflectionMethod $source, string $name): array
    {
        return $source->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * @template T
     *
     * @param class-string<T> $name
     *
     * @return list<T>
     */
    private static function extractAttributesFromProvidedData(TestCase $testCase, string $name): array
    {
        $providedData = self::getProvidedData($testCase);
        $wasList = array_is_list($providedData);
        $attributes = [];

        foreach ($providedData as $key => $data) {
            if ($data instanceof $name) {
                $attributes[] = $data;

                // remove them from the arguments passed to the test method
                unset($providedData[$key]);
            }
        }

        if ($providedData && $wasList) {
            $providedData = array_values($providedData);
        }

        (new \ReflectionProperty(TestCase::class, 'data'))->setValue($testCase, $providedData);

        return $attributes;
    }
}
