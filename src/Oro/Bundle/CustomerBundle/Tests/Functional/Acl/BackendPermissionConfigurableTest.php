<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Acl;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleACLData;
use Oro\Bundle\UserBundle\Entity\AbstractRole;
use Oro\Bundle\UserBundle\Tests\Functional\Acl\AbstractPermissionConfigurableTestCase;

class BackendPermissionConfigurableTest extends AbstractPermissionConfigurableTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
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
            'default false' => [
                'config' => [
                    'commerce' => [
                        'default' => false
                    ]
                ],
                'action' => 'action:oro_frontend_action_test',
                'expected' => false
            ],
            'allow configure permission on oro_frontend_action_test' => [
                'config' => [
                    'commerce' => [
                        'default' => false,
                        'capabilities' => [
                            'oro_frontend_action_test' => true
                        ]
                    ]
                ],
                'action' => 'action:oro_frontend_action_test',
                'expected' => true
            ],
            'disallow configure permission on oro_frontend_action_test' => [
                'config' => [
                    'commerce' => [
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
                    'commerce' => [
                        'default' => true
                    ]
                ],
                'assertGridData' => function ($gridData) {
                    $this->assertHasEntityPermission($gridData, Customer::class, 'VIEW');
                }
            ],
            'default false' => [
                'config' => [
                    'commerce' => [
                        'default' => false
                    ]
                ],
                'assertGridData' => function ($gridData) {
                    $this->assertEmpty($gridData);
                }
            ],
            'enable view permission' => [
                'config' => [
                    'commerce' => [
                        'default' => false,
                        'entities' => [
                            Customer::class => [
                                'VIEW' => true
                            ]
                        ]
                    ]
                ],
                'assertGridData' => function (array $gridData) {
                    $this->assertCount(1, $gridData);
                    $this->assertHasEntityPermission($gridData, Customer::class, 'VIEW');
                    $this->assertNotHasEntityPermission($gridData, Customer::class, 'CREATE');
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
        return 'customer-user-role-permission-grid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteName(): string
    {
        return 'oro_customer_customer_user_role_view';
    }
}
