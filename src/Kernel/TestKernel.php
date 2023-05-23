<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Kernel;

use Pimcore\Kernel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class TestKernel extends Kernel
{
    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'session' => [
                'storage_factory_id' => 'session.storage.factory.mock_file',
            ],
        ]);

        $container->parameters()->set('secret', 'ThisTokenIsNotSoSecretChangeIt');

        $container->import(__DIR__.'/../../dist/config/doctrine.yaml');
        $container->import(__DIR__.'/../../dist/config/security.yaml');

        parent::configureContainer($container);
    }
}
