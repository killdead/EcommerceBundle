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
		 ->arrayNode('company_data')
		   ->children()
		       ->scalarNode('name')->end()
		       ->scalarNode('email')->end()
		   ->end()
                 ->end()
		 ->arrayNode('client_purchase_email')
		   ->children()
		       ->scalarNode('text_1')->end()
		       ->scalarNode('text_2')->end()
		   ->end()
                 ->end()
        ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
