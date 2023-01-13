<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
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

    const USER_MENU_SHOW_ITEMS_ALL_AT_ONCE = 'all_at_once';
    const USER_MENU_SHOW_ITEMS_SUBITEMS_IN_POPUP = 'subitems_in_popup';

    const SECONDS_IN_DAY = 86400;

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
                'default_customer_owner' => ['type' => 'string', 'value' => 1],
                'anonymous_customer_group' => ['type' => 'integer', 'value' => null],
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
                'user_menu_show_items' => ['type' => 'string', 'value' => self::USER_MENU_SHOW_ITEMS_ALL_AT_ONCE],
                'enable_responsive_grids' => ['type' => 'boolean', 'value' => true],
                'enable_swipe_actions_grids' => ['type' => 'boolean', 'value' => true],
                'customer_visitor_cookie_lifetime_days' => ['type' => 'integer', 'value' => 30],
                'maps_enabled' => ['type' => 'boolean', 'value' => true],
                'api_key_generation_enabled' => ['type' => 'boolean', 'value' => true],
                'case_insensitive_email_addresses_enabled' => ['type' => 'boolean', 'value' => false],
            ]
        );

        $rootNode
            ->children()
                ->arrayNode('reset')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        // reset password token ttl, sec
                        ->scalarNode('ttl')
                            ->defaultValue(86400) // 24 hours
                        ->end()
                    ->end()
                ->end()
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
                ->end()
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
                ->end()
            ->end();

        return $treeBuilder;
    }
}
