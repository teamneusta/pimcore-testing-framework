<?php
declare(strict_types=1);

namespace Fixtures\ConfigurationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder('configuration');

        $tree->getRootNode()
            ->children()
                ->scalarNode('foo')->isRequired()->end()
                ->arrayNode('bar')
                    ->isRequired()
                    ->scalarPrototype()
                ->end()
            ->end();

        return $tree;
    }
}
