<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Internal;

use PHPUnit\Framework\TestCase;

/** @internal */
final class AttributeProvider
{
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
        $method = $class->getMethod($testCase->getName(false));

        $attributes = [
            ...self::doGetAttributes($class, $name),
            ...self::doGetAttributes($method, $name),
            ...self::getAttributesFromProvidedData($testCase, $name),
        ];

        // remove them from the arguments passed to the test method
        self::removeAttributesFromProvidedData($testCase, $name);

        return $attributes;
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
    private static function getAttributesFromProvidedData(TestCase $testCase, string $name): array
    {
        $attributes = [];
        foreach ($testCase->getProvidedData() as $data) {
            if ($data instanceof $name) {
                $attributes[] = $data;
            }
        }

        return $attributes;
    }

    /**
     * @param class-string $name
     */
    private static function removeAttributesFromProvidedData(TestCase $testCase, string $name): void
    {
        if ([] === $providedData = $testCase->getProvidedData()) {
            return;
        }

        $filteredData = array_values(array_filter($providedData, fn ($data) => !$data instanceof $name));

        (new \ReflectionProperty(TestCase::class, 'data'))->setValue($testCase, $filteredData);
    }
}
