<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('allegro_sites');

        $rootNode->children()
            ->arrayNode('format')->children()
                ->enumNode('urls')->values(array('absolute', 'relative'))
                        ->defaultValue('absolute')
                        ->info('txt or html')
                ->end()
            ->end()->end()

            ->arrayNode('emails')->children()
                ->arrayNode('contact')->children()
                    ->enumNode('format')->values(array('txt', 'html'))
                            ->defaultValue('html')
                            ->info('txt or html')
                    ->end()
                    ->ScalarNode('email')->defaultValue('%mailer_user%')->end()
                ->end()
            ->end()->end()
        ;

        return $treeBuilder;
    }
}
