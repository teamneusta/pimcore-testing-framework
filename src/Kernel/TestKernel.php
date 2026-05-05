<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Kernel;

use Pimcore\Bundle\AdminBundle\PimcoreAdminBundle;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Pimcore\Kernel;
use Pimcore\Version;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestKernel extends Kernel
{
    private bool $dynamicCache = false;
    /** @var list<class-string<BundleInterface>> */
    private array $testBundles = [];
    /** @var list<string|callable(ContainerBuilder):void> */
    private array $testConfigs = [];
    /** @var list<string|callable(RoutingConfigurator):void> */
    private array $testRoutes = [];
    /** @var array<string, mixed> */
    private array $testExtensionConfigs = [];
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
     * @param string|callable(RoutingConfigurator):void $config path to a config file or a callable which gets the {@see RoutingConfigurator} as its first argument
     */
    public function addTestRoute(string|callable $config): void
    {
        $this->testRoutes[] = $config;
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

    protected function registerCoreBundlesToCollection(BundleCollection $collection): void
    {
        parent::registerCoreBundlesToCollection($collection);

        $collection->addBundle(new PimcoreAdminBundle(), 60);
    }

    protected function configureContainer(
        ContainerConfigurator $container,
        ?LoaderInterface $loader = null,
        ?ContainerBuilder $builder = null,
    ): void {
        \assert(null !== $loader, 'Loader must be set to configure the container.');
        \assert(null !== $builder, 'Container builder must be set to configure the container.');

        $pimcoreVersion = Version::getMajorVersion();

        $container->import(__DIR__ . '/../../dist/config/*.yaml');
        $container->import(__DIR__ . "/../../dist/pimcore{$pimcoreVersion}/config/*.yaml");

        // @phpstan-ignore arguments.count (parent method has only one parameter since Symfony 7.4.9, but we still need to support lower versions)
        parent::configureContainer($container, $loader, $builder);

        if (file_exists($pimcoreVersionConfig = $this->getProjectDir() . "/config/pimcore{$pimcoreVersion}")) {
            $container->import($pimcoreVersionConfig . '/*.{php,yaml}');
        }

        foreach ($this->testConfigs as $config) {
            $loader->load($config);
        }

        foreach ($this->testExtensionConfigs as $namespace => $config) {
            $container->extension($namespace, $config);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        parent::configureRoutes($routes);

        foreach ($this->testRoutes as $route) {
            if (\is_callable($route)) {
                $route($routes);
            } else {
                $routes->import($route);
            }
        }
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
            array_map(static fn ($config) => \is_callable($config) ? self::closureHash($config(...)) : $config, $this->testConfigs),
            array_map(static fn ($config) => \is_callable($config) ? self::closureHash($config(...)) : $config, $this->testRoutes),
            $this->testExtensionConfigs,
            array_map(static fn ($pass) => [$pass[0]::class, $pass[1], $pass[2]], $this->testCompilerPasses),
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
