<?php

namespace Oro\Bundle\FrontendBundle\Layout\Extension;

use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfigurationExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Adds "icons" section to the layout theme configuration.
 * The configuration can be loaded from the following files:
 * * Resources/views/layouts/{theme}/theme.yml
 * * Resources/views/layouts/{theme}/config/icons.yml
 */
class IconsThemeConfigurationExtension implements ThemeConfigurationExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigFileNames(): array
    {
        return ['icons.yml'];
    }

    /**
     * {@inheritdoc}
     */
    public function appendConfig(NodeBuilder $configNode): void
    {
        $configNode
            ->arrayNode('icons')
            ->children()
                ->arrayNode('fa_to_svg')
                    ->normalizeKeys(false)
                    ->scalarPrototype()->end()
                ->end()
            ->end()
            ->children()
                ->arrayNode('file_icons')
                    ->normalizeKeys(false)
                    ->scalarPrototype()->end()
                ->end()
            ->end();
    }
}
