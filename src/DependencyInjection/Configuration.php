<?php

namespace Adamski\Symfony\ScheduleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface {

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root("schedule");

        $rootNode
            ->children()
                ->scalarNode("manager")->defaultValue(null)->end()
            ->end();

        return $treeBuilder;
    }
}
