<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Reflection;

use Neusta\Pimcore\TestingFramework\Test\Attribute\KernelConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TestAttributeProvider
{
    private \ReflectionMethod $test;

    public function __construct(TestCase $test)
    {
        $this->test = new \ReflectionMethod($test, $test->getName(false));
    }

    /**
     * @return list<KernelConfiguration>
     */
    public function getKernelConfigurationAttributes(): array
    {
        $attributes = [];
        foreach ($this->getAttributes($this->test->getDeclaringClass(), KernelConfiguration::class) as $attribute) {
            $attributes[] = $attribute->newInstance();
        }

        foreach ($this->getAttributes($this->test, KernelConfiguration::class) as $attribute) {
            $attributes[] = $attribute->newInstance();
        }

        return $attributes;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return iterable<\ReflectionAttribute<T>>
     */
    private function getAttributes(\ReflectionClass|\ReflectionMethod $source, string $attribute): iterable
    {
        yield from $source->getAttributes($attribute, \ReflectionAttribute::IS_INSTANCEOF);
    }
}
