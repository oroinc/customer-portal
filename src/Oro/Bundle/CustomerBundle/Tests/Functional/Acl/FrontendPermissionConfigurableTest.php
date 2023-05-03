<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Acl;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleACLData;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData as OroLoadCustomerUserData;
use Oro\Bundle\UserBundle\Entity\AbstractRole;
use Oro\Bundle\UserBundle\Tests\Functional\Acl\AbstractPermissionConfigurableTestCase;

class FrontendPermissionConfigurableTest extends AbstractPermissionConfigurableTestCase
{
    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(OroLoadCustomerUserData::AUTH_USER, OroLoadCustomerUserData::AUTH_PW)
        );

        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCustomerUserRoleACLData::class]);

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    public function configurablePermissionCapabilitiesProvider(): array
    {
        return [
            'default true' => [
                'config' => [
                    'commerce_frontend' => [
                        'default' => true
                    ]
                ],
                'action' => 'action:oro_frontend_action_test',
                'expected' => true
            ],
            'disallow configure action oro_frontend_action_test' => [
                'config' => [
                    'commerce_frontend' => [
                        'default' => true,
                        'capabilities' => [
                            'oro_frontend_action_test' => false
                        ]
                    ]
                ],
                'action' => 'action:oro_frontend_action_test',
                'expected' => false
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function configurablePermissionEntitiesProvider(): array
    {
        return [
            'default true' => [
                'config' => [
                    'commerce_frontend' => [
                        'default' => true
                    ]
                ],
                'assertGridData' => function ($gridData) {
                    $this->assertHasEntityPermission($gridData, CustomerUser::class, 'VIEW');
                }
            ],
            'default false' => [
                'config' => [
                    'commerce_frontend' => [
                        'default' => false
                    ]
                ],
                'assertGridData' => function ($gridData) {
                    $this->assertEmpty($gridData);
                }
            ],
            'enable view permission' => [
                'config' => [
                    'commerce_frontend' => [
                        'default' => false,
                        'entities' => [
                            CustomerUser::class => [
                                'VIEW' => true
                            ]
                        ]
                    ]
                ],
                'assertGridData' => function (array $gridData) {
                    $this->assertCount(1, $gridData);
                    $this->assertHasEntityPermission($gridData, CustomerUser::class, 'VIEW');
                    $this->assertNotHasEntityPermission($gridData, CustomerUser::class, 'CREATE');
                }
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRole(): AbstractRole
    {
        return $this->getReference(LoadCustomerUserRoleACLData::ROLE_WITHOUT_ACCOUNT_1_USER_LOCAL);
    }

    /**
     * {@inheritdoc}
     */
    protected function getGridName(): string
    {
        return 'frontend-customer-user-role-permission-grid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteName(): string
    {
        return 'oro_customer_frontend_customer_user_role_view';
    }
}
