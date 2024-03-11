<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Kernel\Internal;

use Pimcore\Kernel;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
    /** @var list<string> */
    private array $testConfigs = [];
    /** @var array<string, array> */
    private array $testExtensionConfigs = [];
    /** @var list<array{CompilerPassInterface, string, int}> */
    private array $testCompilerPasses = [];

    /**
     * @param class-string $bundleClass
     */
    public function addTestBundle(string $bundleClass): void
    {
        $this->testBundles[] = $bundleClass;
        $this->dynamicCache = true;
    }

    /**
     * @param string $config path to a config file
     */
    public function addTestConfig(string $config): void
    {
        $this->testConfigs[] = $config;
        $this->dynamicCache = true;
    }

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
