<?php

namespace Oro\Bundle\CommerceMenuBundle\Layout\Extension;

use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfigurationExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Adds "menu_templates" section to the layout theme configuration.
 *
 * The menu templates configuration can be loaded from the following files:
 * * Resources/views/layouts/{folder}/theme.yml
 */
class MenuTemplatesThemeConfigurationExtension implements ThemeConfigurationExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigFileNames(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function appendConfig(NodeBuilder $configNode): void
    {
        $configNode
            ->arrayNode('menu_templates')
                ->prototype('array')
                    ->children()
                        ->scalarNode('label')->cannotBeEmpty()->end()
                        ->scalarNode('template')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->validate()
                    ->always(
                        function ($templates) {
                            foreach ($templates as $key => $template) {
                                if (!array_key_exists('template', $template)) {
                                    $templates[$key]['template'] = $key;
                                }
                            }

                            return $templates;
                        }
                    )
                ->end()
            ->end();
    }
}
