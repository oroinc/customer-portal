<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CustomerBundle\DependencyInjection\OroCustomerExtension;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroCustomerExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroCustomerExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertSame(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'default_customer_owner' => ['value' => 1, 'scope' => 'app'],
                        'anonymous_customer_group'  => ['value' => null, 'scope' => 'app'],
                        'registration_allowed'  => ['value' => true, 'scope' => 'app'],
                        'registration_link_enabled'  => ['value' => true, 'scope' => 'app'],
                        'confirmation_required'  => ['value' => true, 'scope' => 'app'],
                        'auto_login_after_registration'  => ['value' => false, 'scope' => 'app'],
                        'registration_instructions_enabled'  => ['value' => false, 'scope' => 'app'],
                        'registration_instructions_text'  => [
                            'value' => 'To register for a new account,'
                                . ' contact a sales representative at 1 (800) 555-0123',
                            'scope' => 'app'
                        ],
                        'company_name_field_enabled'  => ['value' => true, 'scope' => 'app'],
                        'user_menu_show_items'  => ['value' => 'all_at_once', 'scope' => 'app'],
                        'enable_responsive_grids'  => ['value' => true, 'scope' => 'app'],
                        'enable_swipe_actions_grids'  => ['value' => true, 'scope' => 'app'],
                        'customer_visitor_cookie_lifetime_days'  => ['value' => 30, 'scope' => 'app'],
                        'maps_enabled'  => ['value' => true, 'scope' => 'app'],
                        'api_key_generation_enabled'  => ['value' => true, 'scope' => 'app'],
                        'case_insensitive_email_addresses_enabled'  => ['value' => false, 'scope' => 'app'],
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_customer')
        );

        self::assertSame([], $container->getParameter('oro_customer_user.login_sources'));
        self::assertSame(86400, $container->getParameter('oro_customer_user.reset.ttl'));

        $customerVisitorCookieFactoryDef = $container->getDefinition(
            'oro_customer.authentication.customer_visitor_cookie_factory'
        );
        self::assertEquals('auto', $customerVisitorCookieFactoryDef->getArgument(0));
        self::assertTrue($customerVisitorCookieFactoryDef->getArgument(1));
        self::assertEquals('lax', $customerVisitorCookieFactoryDef->getArgument(3));
    }

    public function testPrepend(): void
    {
        $container = new ExtendedContainerBuilder();
        $container->setExtensionConfig('security', [
            [
                'firewalls' => [
                    'frontend' => ['frontend_config'],
                    'frontend_secure' => ['frontend_secure_config'],
                    'main' => ['main_config'],
                ]
            ]
        ]);

        $extension = new OroCustomerExtension();
        $extension->prepend($container);

        self::assertSame(
            [
                [
                    'firewalls' => [
                        'main' => ['main_config'],
                        'frontend_secure' => ['frontend_secure_config'],
                        'frontend' => ['frontend_config'],
                    ]
                ]
            ],
            $container->getExtensionConfig('security')
        );
    }
}
