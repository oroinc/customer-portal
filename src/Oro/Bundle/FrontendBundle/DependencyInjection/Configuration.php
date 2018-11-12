<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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
        $rootNodeChildren = $rootNode->children();
        $rootNodeChildren
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
                ->prototype('scalar')->end()
            ->end();
        $frontendApiChildren = $rootNodeChildren
            ->arrayNode('frontend_api')
                ->info('The configuration of API for the storefront')
                ->addDefaultsIfNotSet()
                ->children();
        $frontendApiChildren
            ->arrayNode('api_doc_views')
                ->info('The API views that are available for the storefront')
                ->prototype('scalar')->end()
            ->end();
        $this->appendFrontendApiCorsNode($frontendApiChildren);

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $node
     */
    private function appendFrontendApiCorsNode(NodeBuilder $node)
    {
        $node
            ->arrayNode('cors')
                ->info('The configuration of CORS requests for the storefront')
                ->addDefaultsIfNotSet()
                ->children()
                    ->integerNode('preflight_max_age')
                        ->info('The amount of seconds the user agent is allowed to cache CORS preflight requests')
                        ->defaultValue(600)
                        ->min(0)
                    ->end()
                    ->arrayNode('allow_origins')
                        ->info('The list of origins that are allowed to send CORS requests')
                        ->example(['https://foo.com', 'https://bar.com'])
                        ->prototype('scalar')->cannotBeEmpty()->end()
                    ->end()
                    ->booleanNode('allow_credentials')
                        ->info('Indicates whether CORS request can include user credentials')
                        ->defaultValue(false)
                    ->end()
                    ->arrayNode('allow_headers')
                        ->info('The list of headers that are allowed to send by CORS requests')
                        ->example(['X-Foo', 'X-Bar'])
                        ->prototype('scalar')->cannotBeEmpty()->end()
                    ->end()
                    ->arrayNode('expose_headers')
                        ->info('The list of headers that can be exposed by CORS responses')
                        ->example(['X-Foo', 'X-Bar'])
                        ->prototype('scalar')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();
    }
}
