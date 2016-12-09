<?php

namespace Ziiweb\EcommerceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ziiweb_ecommerce');

        $rootNode
            ->children()
                 ->arrayNode('company_data')->canBeDisabled()
                   ->children()
                       ->scalarNode('name')->end()
                       ->scalarNode('email')->end()
                       ->scalarNode('domain')->end()
                   ->end()
                         ->end()
                 ->arrayNode('client_purchase_email')->canBeDisabled()
                   ->children()
                       ->scalarNode('text_1')->end()
                       ->scalarNode('text_2')->end()
                   ->end()
                         ->end()
                 ->arrayNode('redsys')->canBeDisabled()
                   ->children()
                       ->arrayNode('test')
                         ->children()
                       ->scalarNode('key')->end()
                       ->scalarNode('merchant_code')->end()
                       ->scalarNode('merchant_terminal')->end()
                     ->end()
                    ->end()
                    ->arrayNode('live')
                    ->children()
                       ->scalarNode('key')->end()
                       ->scalarNode('merchant_code')->end()
                       ->scalarNode('merchant_terminal')->end()
                       ->end()
                       ->end()
                ->end()
            ->end()
        ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
