<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Kernel;

use Pimcore\Bundle\AdminBundle\PimcoreAdminBundle;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Pimcore\Kernel;
use Pimcore\Version;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/** @internal */
abstract class CompatibilityKernel extends Kernel
{
    /**
     * @internal
     *
     * @var list<string|callable(ContainerBuilder):void>
     */
    protected array $testConfigs = [];
    /**
     * @internal
     *
     * @var array<string, mixed>
     */
    protected array $testExtensionConfigs = [];

    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder,
    ): void {
        $pimcoreVersion = Version::getMajorVersion();

        $container->import(__DIR__ . '/../../dist/config/*.yaml');
        $container->import(__DIR__ . "/../../dist/pimcore{$pimcoreVersion}/config/*.yaml");

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

    protected function registerCoreBundlesToCollection(BundleCollection $collection): void
    {
        parent::registerCoreBundlesToCollection($collection);

        $collection->addBundle(new PimcoreAdminBundle(), 60);
    }
}
