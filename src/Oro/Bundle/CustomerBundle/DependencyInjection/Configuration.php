<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Cookie;

class Configuration implements ConfigurationInterface
{
    public const ROOT_NODE = 'oro_customer';

    /**
     * @internal
     */
    const DEFAULT_REGISTRATION_INSTRUCTIONS_TEXT
        = 'To register for a new account, contact a sales representative at 1 (800) 555-0123';

    /** Start Requirement for "Default Theme 50/51" */
    const USER_MENU_SHOW_ITEMS_ALL_AT_ONCE = 'all_at_once';
    const USER_MENU_SHOW_ITEMS_SUBITEMS_IN_POPUP = 'subitems_in_popup';
    /** End Requirement for "Default Theme 50/51" */
    const SECONDS_IN_DAY = 86400;

    public const ANONYMOUS_CUSTOMER_GROUP = 'anonymous_customer_group';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                'default_customer_owner' => ['type' => 'string', 'value' => 1],
                self::ANONYMOUS_CUSTOMER_GROUP => ['type' => 'integer', 'value' => null],
                'registration_allowed' => ['type' => 'boolean', 'value' => true],
                'registration_link_enabled' => ['type' => 'boolean', 'value' => true],
                'confirmation_required' => ['type' => 'boolean', 'value' => true],
                'auto_login_after_registration' => ['type' => 'boolean', 'value' => false],
                'registration_instructions_enabled' => ['type' => 'boolean', 'value' => false],
                'registration_instructions_text' => [
                    'type' => 'textarea',
                    'value' => self::DEFAULT_REGISTRATION_INSTRUCTIONS_TEXT,
                ],
                'company_name_field_enabled' => ['type' => 'boolean', 'value' => true],
                /** Start Requirement for "Default Theme 50/51" */
                'user_menu_show_items' => ['type' => 'string', 'value' => self::USER_MENU_SHOW_ITEMS_ALL_AT_ONCE],
                /** End Requirement for "Default Theme 50/51" */
                'enable_responsive_grids' => ['type' => 'boolean', 'value' => true],
                'enable_swipe_actions_grids' => ['type' => 'boolean', 'value' => true],
                'customer_visitor_cookie_lifetime_days' => ['type' => 'integer', 'value' => 30],
                'create_customer_visitor_immediately' => ['type' => 'integer', 'value' => true],
                'maps_enabled' => ['type' => 'boolean', 'value' => true],
                'non_authenticated_visitors_api' => ['type' => 'boolean', 'value' => false],
                'api_key_generation_enabled' => ['type' => 'boolean', 'value' => true],
                'case_insensitive_email_addresses_enabled' => ['type' => 'boolean', 'value' => false],
                'email_enumeration_protection_enabled' => ['type' => 'boolean', 'value' => false],
                'customer_user_login_password' => ['type' => 'boolean', 'value' => true]
            ]
        );

        $rootNodeChildren = $rootNode->children();
        $this->appendResetNode($rootNodeChildren);
        $this->appendVisitorSessionNode($rootNodeChildren);
        $this->appendLoginSourcesNode($rootNodeChildren);
        $this->appendFrontendApiNode($rootNodeChildren);

        return $treeBuilder;
    }

    private function appendResetNode(NodeBuilder $node): void
    {
        $node
            ->arrayNode('reset')
                ->addDefaultsIfNotSet()
                ->canBeUnset()
                ->children()
                    // reset password token ttl, sec
                    ->scalarNode('ttl')
                        ->defaultValue(86400) // 24 hours
                    ->end()
                ->end()
            ->end();
    }

    private function appendVisitorSessionNode(NodeBuilder $node): void
    {
        $node
            ->arrayNode('visitor_session')
                // More info about visitor cookie configuration can be found at
                // https://doc.oroinc.com/backend/setup/post-install/cookies-configuration/#customer-visitor-cookie
                ->addDefaultsIfNotSet()
                ->children()
                    ->enumNode('cookie_secure')->values([true, false, 'auto'])->defaultValue('auto')->end()
                    ->booleanNode('cookie_httponly')->defaultTrue()->end()
                    ->enumNode('cookie_samesite')
                        ->values([null, Cookie::SAMESITE_LAX, Cookie::SAMESITE_STRICT, Cookie::SAMESITE_NONE])
                        ->defaultValue(Cookie::SAMESITE_LAX)
                        ->end()
                    ->end()
            ->end();
    }

    private function appendLoginSourcesNode(NodeBuilder $node): void
    {
        $node
            ->arrayNode('login_sources')
                ->validate()
                    ->always(function (array $value) {
                        foreach ($value as $name => $config) {
                            foreach ($value as $innerName => $innerConfig) {
                                if ($name === $innerName) {
                                    continue;
                                }
                                if ($config['code'] === $innerConfig['code']) {
                                    throw new \LogicException(sprintf(
                                        'The "code" option for "%s" and "%s" login sources are duplicated.',
                                        $name,
                                        $innerName
                                    ));
                                }
                            }
                        }

                        return $value;
                    })
                ->end()
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('label')->end()
                        ->integerNode('code')->end()
                    ->end()
                ->end()
            ->end();
    }

    public static function getConfigKeyByName(string $name): string
    {
        return TreeUtils::getConfigKey(static::ROOT_NODE, $name);
    }

    private function appendFrontendApiNode(NodeBuilder $node): void
    {
        $node
            ->arrayNode('frontend_api')
                ->info('The configuration of API for the storefront.')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('non_authenticated_visitors_api_resources')
                        ->info('The list of entities that should be available for non-authenticated visitors.')
                        ->example(['Acme\AppBundle\Entity\Product'])
                        ->scalarPrototype()
                    ->end()
                ->end()
            ->end();
    }
}
