<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\DependencyInjection\OroCustomerExtension;
use Oro\Bundle\CustomerBundle\Form\Type\RedirectAfterLoginConfigType;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class OroCustomerExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroCustomerExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertEquals(
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
                        /** Start Requirement for "Default Theme 50/51" */
                        'user_menu_show_items'  => ['value' => 'all_at_once', 'scope' => 'app'],
                        /** End Requirement for "Default Theme 50/51" */
                        'enable_responsive_grids'  => ['value' => true, 'scope' => 'app'],
                        'enable_swipe_actions_grids'  => ['value' => true, 'scope' => 'app'],
                        'customer_visitor_cookie_lifetime_days'  => ['value' => 30, 'scope' => 'app'],
                        'maps_enabled'  => ['value' => true, 'scope' => 'app'],
                        'non_authenticated_visitors_api'  => ['value' => false, 'scope' => 'app'],
                        'case_insensitive_email_addresses_enabled'  => ['value' => false, 'scope' => 'app'],
                        'email_enumeration_protection_enabled' => ['value' => true, 'scope' => 'app'],
                        'validate_shipping_addresses__my_account' => ['value' => true, 'scope' => 'app'],
                        'validate_billing_addresses__my_account' => ['value' => false, 'scope' => 'app'],
                        'validate_shipping_addresses__backoffice' => ['value' => true, 'scope' => 'app'],
                        'validate_billing_addresses__backoffice' => ['value' => false, 'scope' => 'app'],
                        Configuration::REDIRECT_AFTER_LOGIN => [
                            'value' => ['targetType' => RedirectAfterLoginConfigType::TARGET_NONE],
                            'scope' => 'app'
                        ],
                        Configuration::DO_NOT_LEAVE_CHECKOUT => ['value' => true, 'scope' => 'app'],
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_customer')
        );

        self::assertSame([], $container->getParameter('oro_customer_user.login_sources'));
        self::assertSame(86400, $container->getParameter('oro_customer_user.reset.ttl'));

        $cookieFactoryDef = $container->getDefinition('oro_customer.authentication.customer_visitor_cookie_factory');
        self::assertEquals('auto', $cookieFactoryDef->getArgument(0));
        self::assertTrue($cookieFactoryDef->getArgument(1));
        self::assertEquals('lax', $cookieFactoryDef->getArgument(3));

        self::assertSame(
            [],
            $container->getDefinition('oro_customer.authentication.decision_maker.api_anonymous_customer_user')
                ->getArgument('$apiResources')
        );
        self::assertSame(
            [],
            $container->getDefinition('oro_customer.api_doc.documentation_provider.non_authenticated_visitors')
                ->getArgument('$apiResources')
        );
    }

    public function testLoadWithNonAuthenticatedVisitorsApiConfig(): void
    {
        $config = [
            'frontend_api' => [
                'non_authenticated_visitors_api_resources' => ['Test\Entity']
            ]
        ];

        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroCustomerExtension();
        $extension->load([$config], $container);

        self::assertSame(
            $config['frontend_api']['non_authenticated_visitors_api_resources'],
            $container->getDefinition('oro_customer.authentication.decision_maker.api_anonymous_customer_user')
                ->getArgument('$apiResources')
        );
        self::assertSame(
            $config['frontend_api']['non_authenticated_visitors_api_resources'],
            $container->getDefinition('oro_customer.api_doc.documentation_provider.non_authenticated_visitors')
                ->getArgument('$apiResources')
        );
    }

    public function testPrepend(): void
    {
        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
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
