<?php
declare(strict_types=1);

namespace Fixtures\ConfigurationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DeregisterSomethingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('something')) {
            $container->removeDefinition('something');
        }
    }
}
