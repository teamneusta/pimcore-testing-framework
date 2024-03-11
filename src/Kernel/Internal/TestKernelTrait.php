<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Kernel\Internal;

use Pimcore\Kernel;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 *
 * @mixin Kernel
 */
trait TestKernelTrait
{
    private bool $dynamicCache = false;
    /** @var list<string> */
    private array $testBundles = [];
    /** @var array<string, array> */
    private array $testExtensionConfigs = [];

    /**
     * @param class-string $bundleClass
     */
    public function addTestBundle(string $bundleClass): void
    {
        $this->testBundles[] = $bundleClass;
        $this->dynamicCache = true;
    }

    public function addTestExtensionConfig(string $namespace, array $config): void
    {
        $this->testExtensionConfigs[$namespace] = $config;
        $this->dynamicCache = true;
    }

    public function handleOptions(array $options): void
    {
        if (\is_callable($configure = $options['config'] ?? null)) {
            $configure($this);
        }
    }

    public function getCacheDir(): string
    {
        if ($this->dynamicCache) {
            return rtrim(parent::getCacheDir(), '/') . '/' . spl_object_hash($this);
        }

        return parent::getCacheDir();
    }

    public function registerBundles(): array
    {
        $bundles = parent::registerBundles();

        if ([] === $this->testBundles) {
            return $bundles;
        }

        $bundleClasses = [];
        foreach ($bundles as $bundle) {
            $bundleClasses[$bundle::class] = true;
        }

        foreach (array_unique($this->testBundles) as $class) {
            if (!isset($bundleClasses[$class])) {
                $bundles[] = new $class();
            }
        }

        return $bundles;
    }

    public function shutdown(): void
    {
        parent::shutdown();

        if ($this->dynamicCache) {
            (new Filesystem())->remove($this->getCacheDir());
        }
    }
}
