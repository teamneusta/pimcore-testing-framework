<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Kernel;

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
            $container->import(__DIR__.'/../../dist/config/*.yaml');
            $container->import(__DIR__.'/../../dist/pimcore10/config/*.yaml');

            parent::configureContainer($container);
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

            $container->import(__DIR__.'/../../dist/config/*.yaml');
            $container->import(__DIR__.'/../../dist/pimcore11/config/*.yaml');

            parent::configureContainer($container, $loader, $builder);
        }
    }
}
