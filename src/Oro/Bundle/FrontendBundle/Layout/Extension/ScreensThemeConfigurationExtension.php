<?php

namespace Oro\Bundle\FrontendBundle\Layout\Extension;

use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfigurationExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Adds "screens" section to the layout theme configuration.
 * The screens configuration can be loaded from the following files:
 * * Resources/views/layouts/{folder}/theme.yml
 * * Resources/views/layouts/{folder}/config/screens.yml
 */
class ScreensThemeConfigurationExtension implements ThemeConfigurationExtensionInterface
{
    /**
     * @return string[]
     */
    public function getConfigFileNames(): array
    {
        return ['screens.yml'];
    }

    /**
     * {@inheritdoc}
     */
    public function appendConfig(NodeBuilder $configNode)
    {
        $configNode
            ->arrayNode('screens')
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('label')->cannotBeEmpty()->end()
                        ->scalarNode('hidingCssClass')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();
    }
}
