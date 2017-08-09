<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection;

use Oro\Bundle\LayoutBundle\DependencyInjection\OroLayoutExtension;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration class which was made specifically to handle screens configuration, which should be inserted into
 * oro_layout.themes.*.config.screens configuration node.
 *
 * @see OroFrontendExtension::prependScreensConfigs()
 */
class ScreensConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(OroLayoutExtension::ALIAS);
        $rootNode
            ->children()
                ->arrayNode('themes')
                    ->useAttributeAsKey('theme-identifier')
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->children()
                            ->arrayNode('config')
                                ->children()
                                    ->arrayNode('screens')
                                        ->useAttributeAsKey('screen-type-identifier')
                                            ->prototype('array')
                                                ->children()
                                                    ->scalarNode('label')->cannotBeEmpty()->end()
                                                    ->scalarNode('hidingCssClass')->cannotBeEmpty()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
