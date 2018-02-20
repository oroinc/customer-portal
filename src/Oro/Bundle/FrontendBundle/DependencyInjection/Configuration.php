<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const FILTER_VALUE_SELECTORS_ALL_AT_ONCE = 'all_at_once';
    const FILTER_VALUE_SELECTORS_DROPDOWN = 'dropdown';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(OroFrontendExtension::ALIAS);

        SettingsBuilder::append(
            $rootNode,
            [
                'frontend_theme' => ['type' => 'string', 'value' => '%oro_layout.default_active_theme%'],
                'page_templates' => ['type' => 'array', 'value' => []],
                'guest_access_enabled' => ['type' => 'boolean', 'value' => true],
                'filter_value_selectors' => ['type' => 'string', 'value' => self::FILTER_VALUE_SELECTORS_DROPDOWN],
            ]
        );
        $rootNode->children()
            ->booleanNode('debug_routes')
                ->defaultTrue()
            ->end()
            ->arrayNode('routes_to_expose')
                ->beforeNormalization()
                    ->ifTrue(function ($v) {
                        return !is_array($v);
                    })
                    ->then(function ($v) {
                        return [$v];
                    })
                    ->end()
                ->prototype('scalar')
            ->end();

        return $treeBuilder;
    }
}
