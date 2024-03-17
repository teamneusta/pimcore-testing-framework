<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Kernel;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class TestKernel extends CompatibilityKernel
{
    private bool $dynamicCache = false;
    /** @var list<class-string<BundleInterface>> */
    private array $testBundles = [];
    /** @var list<array{CompilerPassInterface, string, int}> */
    private array $testCompilerPasses = [];

    /**
     * @param class-string<BundleInterface> $bundleClass
     */
    public function addTestBundle(string $bundleClass): void
    {
        $this->testBundles[] = $bundleClass;
        $this->dynamicCache = true;
    }

    /**
     * @param string|callable(ContainerBuilder):void $config path to a config file or a callable which get the {@see ContainerBuilder} as its first argument
     */
    public function addTestConfig(string|callable $config): void
    {
        $this->testConfigs[] = $config;
        $this->dynamicCache = true;
    }

    /**
     * @param array<string, array<mixed>> $config
     */
    public function addTestExtensionConfig(string $namespace, array $config): void
    {
        $this->testExtensionConfigs[$namespace] = $config;
        $this->dynamicCache = true;
    }

    /**
     * @param PassConfig::TYPE_* $type
     */
    public function addTestCompilerPass(
        CompilerPassInterface $compilerPass,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = 0,
    ): void {
        $this->testCompilerPasses[] = [$compilerPass, $type, $priority];
        $this->dynamicCache = true;
    }

    /**
     * @param array{config?: callable(static):void, ...} $options
     */
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

    /**
     * @return array<BundleInterface>
     */
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

    protected function buildContainer(): ContainerBuilder
    {
        $container = parent::buildContainer();

        foreach ($this->testCompilerPasses as $compilerPass) {
            $container->addCompilerPass(...$compilerPass);
        }

        return $container;
    }

    public function shutdown(): void
    {
        parent::shutdown();

        if ($this->dynamicCache) {
            (new Filesystem())->remove($this->getCacheDir());
        }
    }
}
