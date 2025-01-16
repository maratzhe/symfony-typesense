<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('typesense');

        $treeBuilder
            ->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('dsn')->defaultValue('http://localhost:8108?api_key=xyz')->end()
                ->arrayNode('paths')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('naming_strategy')->defaultValue('')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
