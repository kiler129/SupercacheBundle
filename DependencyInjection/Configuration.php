<?php

namespace noFlash\SupercacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link
 * http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('supercache');

        //@formatter:off
        $rootNode
            ->children()
                ->booleanNode('enable_prod')
                    ->defaultTrue()
                    ->info('Enable/disable cache while running prod environment')
                ->end()
                ->booleanNode('enable_dev')
                    ->defaultFalse()
                    ->info('Enable/disable cache while running dev environment')
                ->end()
                ->scalarNode('cache_dir')
                    ->defaultValue('%kernel.root_dir%/../webcache')
                    ->info('Cache directory, must be http-accessible (so it cannot be located under app/)')
                ->end()
                ->booleanNode('cache_status_header')
                    ->defaultTrue()
                    ->info('Enable/disable adding X-Supercache header')
                ->end()
            ->end()
        ;
        //@formatter:on

        return $treeBuilder;
    }
}
