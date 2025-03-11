<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Kernel;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
     * @param string|callable(ContainerBuilder):void $config path to a config file or a callable which gets the {@see ContainerBuilder} as its first argument
     */
    public function addTestConfig(string|callable $config): void
    {
        $this->testConfigs[] = $config;
        $this->dynamicCache = true;
    }

    /**
     * @param array<string, mixed> $config
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
            return rtrim(parent::getCacheDir(), '/') . '_' . $this->getTestConfigHash();
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

    private function getTestConfigHash(): string
    {
        return hash('xxh3', json_encode([
            $this->testBundles,
            array_map(fn ($config) => \is_callable($config) ? self::closureHash($config(...)) : $config, $this->testConfigs),
            $this->testExtensionConfigs,
            array_map(fn ($pass) => [$pass[0]::class, $pass[1], $pass[2]], $this->testCompilerPasses),
        ], \JSON_THROW_ON_ERROR));
    }

    private static function closureHash(\Closure $closure): string
    {
        static $hashes;
        $hashes ??= new \SplObjectStorage();

        if (!isset($hashes[$closure])) {
            $ref = new \ReflectionFunction($closure);

            if (false === $fileName = $ref->getFileName()) {
                throw new \RuntimeException('Unable to get the file name of closure ' . $ref);
            }

            $file = new \SplFileObject($fileName);
            $file->seek($ref->getStartLine() - 1);

            $content = '';
            while ($file->key() < $ref->getEndLine()) {
                $content .= $file->current();
                $file->next();
            }

            $hashes[$closure] = hash('xxh3', json_encode([
                $content,
                $ref->getStaticVariables(),
            ], \JSON_THROW_ON_ERROR));
        }

        return $hashes[$closure];
    }
}
