<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class ConfigurationExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $container->setParameter('configuration.foo', $mergedConfig['foo']);
        $container->setParameter('configuration.bar', $mergedConfig['bar']);
    }
}
