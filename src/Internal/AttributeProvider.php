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

    /** @var array<class-string, array<string, list<\ReflectionAttribute>> */
    private static array $classAttributes = [];

    /**
     * @template T
     *
     * @param class-string<T> $name
     *
     * @return list<T>
     */
    public static function getAttributes(TestCase $testCase, string $name): array
    {
        $class = new \ReflectionClass($testCase);

        return [
            ...self::$classAttributes[$testCase::class][$name] ??= self::getClassAttributes($class, $name),
            ...self::doGetAttributes($class->getMethod($testCase->getName(false)), $name),
            ...self::extractAttributesFromProvidedData($testCase, $name),
        ];
    }

    /**
     * @template T
     *
     * @param \ReflectionClass<TestCase> $class
     * @param class-string<T>            $name
     *
     * @return list<T>
     */
    private static function getClassAttributes(\ReflectionClass $class, string $name): array
    {
        $attributes = [self::doGetAttributes($class, $name)];

        while ($class = $class->getParentClass()) {
            if (in_array($class->getName(), self::TOPMOST_TEST_CASES, true)) {
                break;
            }

            $attributes[] = self::doGetAttributes($class, $name);
        }

        return array_merge(...array_reverse($attributes));
    }

    /**
     * @template T
     *
     * @param \ReflectionClass<TestCase>|\ReflectionMethod $source
     * @param class-string<T>                              $name
     *
     * @return list<T>
     */
    private static function doGetAttributes(\ReflectionClass|\ReflectionMethod $source, string $name): array
    {
        $attributes = [];
        foreach ($source->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $attributes[] = $attribute->newInstance();
        }

        return $attributes;
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
        $providedData = $testCase->getProvidedData();
        $attributes = [];

        foreach ($providedData as $key => $data) {
            if ($data instanceof $name) {
                $attributes[] = $data;

                // remove them from the arguments passed to the test method
                unset($providedData[$key]);
            }
        }

        if ($providedData && array_is_list($providedData)) {
            $providedData = array_values($providedData);
        }

        (new \ReflectionProperty(TestCase::class, 'data'))->setValue($testCase, $providedData);

        return $attributes;
    }
}
