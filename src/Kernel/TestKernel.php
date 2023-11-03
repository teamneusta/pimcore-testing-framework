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

if (!method_exists(Version::class, 'getMajorVersion') || 10 === Version::getMajorVersion()) {
    class TestKernel extends Kernel
    {
        protected function configureContainer(ContainerConfigurator $container): void
        {
            $container->import(__DIR__ . '/../../dist/config/*.yaml');
            $container->import(__DIR__ . '/../../dist/pimcore10/config/*.yaml');

            parent::configureContainer($container);

            if (file_exists($pimcore10Config = $this->getProjectDir() . '/config/pimcore10')) {
                $container->import($pimcore10Config . '/*.{php,yaml}');
            }
        }
    }
} else {
    class TestKernel extends Kernel
    {
        protected function configureContainer(
            ContainerConfigurator $container,
            LoaderInterface $loader,
            ContainerBuilder $builder,
        ): void {
            $container->import(__DIR__ . '/../../dist/config/*.yaml');
            $container->import(__DIR__ . '/../../dist/pimcore11/config/*.yaml');

            parent::configureContainer($container, $loader, $builder);

            if (file_exists($pimcore11Config = $this->getProjectDir() . '/config/pimcore11')) {
                $container->import($pimcore11Config . '/*.{php,yaml}');
            }
        }

        protected function registerCoreBundlesToCollection(BundleCollection $collection): void
        {
            if (!class_exists(PimcoreAdminBundle::class)) {
                throw new \LogicException('Pimcore 11 requires the "pimcore/admin-ui-classic-bundle" dependency.');
            }

            parent::registerCoreBundlesToCollection($collection);

            $collection->addBundle(new PimcoreAdminBundle(), 60);
        }
    }
}
