<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Cookie;

class Configuration implements ConfigurationInterface
{
    const ROOT_NODE = 'oro_frontend';
    const FILTER_VALUE_SELECTORS_ALL_AT_ONCE = 'all_at_once';
    const FILTER_VALUE_SELECTORS_DROPDOWN = 'dropdown';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                'frontend_theme' => ['type' => 'string', 'value' => '%oro_layout.default_active_theme%'],
                'page_templates' => ['type' => 'array', 'value' => []],
                'guest_access_enabled' => ['type' => 'boolean', 'value' => true],
                'filter_value_selectors' => ['type' => 'string', 'value' => self::FILTER_VALUE_SELECTORS_DROPDOWN],
                'web_api' => ['type' => 'boolean', 'value' => false]
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
        $this->appendSessionNode($rootNodeChildren);
        $frontendApiChildren = $rootNodeChildren
            ->arrayNode('frontend_api')
                ->info('The configuration of API for the storefront.')
                ->addDefaultsIfNotSet()
                ->children();
        $frontendApiChildren
            ->arrayNode('api_doc_views')
                ->info('The API views that are available for the storefront.')
                ->prototype('scalar')->end()
            ->end();
        $this->appendFrontendApiCorsNode($frontendApiChildren);

        return $treeBuilder;
    }

    private function appendSessionNode(NodeBuilder $node)
    {
        $node
            ->arrayNode('session')
                ->info('The configuration of storefront session.')
                ->children()
                    ->scalarNode('name')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(function ($v) {
                                parse_str($v, $parsed);

                                return implode('&', array_keys($parsed)) !== (string)$v;
                            })
                            ->thenInvalid('Session name %s contains illegal character(s).')
                        ->end()
                    ->end()
                    ->scalarNode('cookie_lifetime')->end()
                    ->scalarNode('cookie_path')->end()
                    ->enumNode('cookie_secure')->values([true, false, 'auto'])->end()
                    ->booleanNode('cookie_httponly')->end()
                    ->enumNode('cookie_samesite')
                        ->values([null, Cookie::SAMESITE_LAX, Cookie::SAMESITE_STRICT, Cookie::SAMESITE_NONE])
                        ->defaultValue(Cookie::SAMESITE_LAX)
                        ->end()
                    ->scalarNode('gc_maxlifetime')->end()
                    ->scalarNode('gc_probability')->end()
                    ->scalarNode('gc_divisor')->end()
                ->end()
            ->end();
    }

    private function appendFrontendApiCorsNode(NodeBuilder $node)
    {
        $node
            ->arrayNode('cors')
                ->info('The configuration of CORS requests for the storefront.')
                ->addDefaultsIfNotSet()
                ->children()
                    ->integerNode('preflight_max_age')
                        ->info('The amount of seconds the user agent is allowed to cache CORS preflight requests.')
                        ->defaultValue(600)
                        ->min(0)
                    ->end()
                    ->arrayNode('allow_origins')
                        ->info('The list of origins that are allowed to send CORS requests.')
                        ->example(['https://foo.com', 'https://bar.com'])
                        ->prototype('scalar')->cannotBeEmpty()->end()
                    ->end()
                    ->booleanNode('allow_credentials')
                        ->info('Indicates whether CORS request can include user credentials.')
                        ->defaultValue(false)
                    ->end()
                    ->arrayNode('allow_headers')
                        ->info('The list of headers that are allowed to send by CORS requests.')
                        ->example(['X-Foo', 'X-Bar'])
                        ->prototype('scalar')->cannotBeEmpty()->end()
                    ->end()
                    ->arrayNode('expose_headers')
                        ->info('The list of headers that can be exposed by CORS responses.')
                        ->example(['X-Foo', 'X-Bar'])
                        ->prototype('scalar')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();
    }
}
