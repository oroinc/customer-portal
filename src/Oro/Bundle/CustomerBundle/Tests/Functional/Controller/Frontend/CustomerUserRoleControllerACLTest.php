<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleACLData;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserRoleControllerACLTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserRoleACLData::class]);
    }

    public function testCreatePermissionDenied()
    {
        $this->loginUser(LoadCustomerUserRoleACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY);
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_role_create'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 403);
    }

    /**
     * @dataProvider viewProvider
     */
    public function testACL(string $route, string $role, string $user, int $expectedStatus)
    {
        $this->loginUser($user);
        /* @var CustomerUserRole $role */
        $role = $this->getReference($role);
        $this->client->request('GET', $this->getUrl(
            $route,
            ['id' => $role->getId()]
        ));

        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, $expectedStatus);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function viewProvider(): array
    {
        return [
            'VIEW (user from parent customer : DEEP)' => [
                'route' => 'oro_customer_frontend_customer_user_role_view',
                'role' => LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_2_USER_LOCAL,
                'user' => LoadCustomerUserRoleACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'expectedStatus' => 200
            ],
            'VIEW (user from another customer)' => [
                'route' => 'oro_customer_frontend_customer_user_role_view',
                'role' => LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_2_USER_LOCAL,
                'user' => LoadCustomerUserRoleACLData::USER_ACCOUNT_2_ROLE_LOCAL,
                'expectedStatus' => 403
            ],
            'VIEW (anonymous user)' => [
                'route' => 'oro_customer_frontend_customer_user_role_view',
                'role' => LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_USER_LOCAL,
                'user' => '',
                'expectedStatus' => 401
            ],
            'VIEW (user from same customer : LOCAL)' => [
                'route' => 'oro_customer_frontend_customer_user_role_view',
                'role' => LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_USER_DEEP,
                'user' => LoadCustomerUserRoleACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'expectedStatus' => 200
            ],
            'UPDATE (user from parent customer : DEEP)' => [
                'route' => 'oro_customer_frontend_customer_user_role_update',
                'role' => LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_2_USER_LOCAL,
                'user' => LoadCustomerUserRoleACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'expectedStatus' => 200
            ],
            'UPDATE (user from another customer)' => [
                'route' => 'oro_customer_frontend_customer_user_role_update',
                'role' => LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_2_USER_LOCAL,
                'user' => LoadCustomerUserRoleACLData::USER_ACCOUNT_2_ROLE_LOCAL,
                'expectedStatus' => 403
            ],
            'UPDATE (anonymous user)' => [
                'route' => 'oro_customer_frontend_customer_user_role_update',
                'role' => LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_USER_LOCAL,
                'user' => '',
                'expectedStatus' => 401
            ],
            'UPDATE (user from same customer : LOCAL)' => [
                'route' => 'oro_customer_frontend_customer_user_role_update',
                'role' => LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_USER_DEEP,
                'user' => LoadCustomerUserRoleACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'expectedStatus' => 200
            ],
        ];
    }

    public function testRolePermissionGrid()
    {
        $this->loginUser(LoadCustomerUserRoleACLData::USER_ACCOUNT_1_ROLE_DEEP);
        /* @var CustomerUserRole $role */
        $role = $this->getReference(LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_USER_DEEP);
        $gridParameters = [
            'gridName' => 'frontend-customer-user-role-permission-grid',
            'role' => $role
        ];
        $container = $this->client->getContainer();
        /** @var Manager $gridManager */
        $gridManager = $container->get('oro_datagrid.datagrid.manager');
        $grid = $gridManager->getDatagridByRequestParams(
            'frontend-customer-user-role-permission-grid',
            $gridParameters
        );
        $result = $grid->getData();
        foreach ($result['data'] as $key) {
            foreach ($key['permissions'] as $permission) {
                self::assertNotEquals(AccessLevel::SYSTEM_LEVEL, $permission['access_level']);
            }
        }
    }

    /**
     * @group frontend-ACL
     * @dataProvider gridAclProvider
     */
    public function testGridACL(string $user, int $indexResponseStatus, int $gridResponseStatus, array $data = [])
    {
        $this->loginUser($user);
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_role_index'));
        $this->assertSame($indexResponseStatus, $this->client->getResponse()->getStatusCode());
        $response = $this->client->requestGrid(
            [
                'gridName' => 'frontend-customer-customer-user-roles-grid',
            ]
        );
        self::assertResponseStatusCodeEquals($response, $gridResponseStatus);
        if (200 === $gridResponseStatus) {
            $result = self::jsonToArray($response->getContent());
            $actual = array_column($result['data'], 'id');
            $actual = array_map('intval', $actual);
            $expected = array_map(
                function ($ref) {
                    return $this->getReference($ref)->getId();
                },
                $data
            );
            sort($expected);
            sort($actual);
            $this->assertEquals($expected, $actual);
        }
    }

    public function gridAclProvider(): array
    {
        return [
            'NOT AUTHORISED' => [
                'user' => '',
                'indexResponseStatus' => 401,
                'gridResponseStatus' => 403,
                'data' => [],
            ],
            'DEEP: all siblings and children' => [
                'user' => LoadCustomerUserRoleACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'indexResponseStatus' => 200,
                'gridResponseStatus' => 200,
                'data' => [
                    LoadCustomerUserRoleACLData::ROLE_FRONTEND_ADMINISTRATOR,
                    LoadCustomerUserRoleACLData::ROLE_FRONTEND_BUYER,
                    LoadCustomerUserRoleACLData::ROLE_DEEP,
                    LoadCustomerUserRoleACLData::ROLE_DEEP_VIEW_ONLY,
                    LoadCustomerUserRoleACLData::ROLE_LOCAL,
                    LoadCustomerUserRoleACLData::ROLE_LOCAL_VIEW_ONLY,
                    LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_USER_LOCAL,
                    LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_USER_DEEP,
                    LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_2_USER_LOCAL,
                    LoadCustomerUserRoleACLData::ROLE_WITHOUT_ACCOUNT_1_USER_LOCAL,
                    LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_USER_LOCAL_CANT_DELETED,
                    LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_USER_DEEP_CANT_DELETED,
                    LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_2_USER_LOCAL_CANT_DELETED,
                    LoadCustomerUserRoleACLData::ROLE_WITHOUT_ACCOUNT_1_USER_LOCAL_CANT_DELETED
                ],
            ],
            'LOCAL: all siblings' => [
                'user' => LoadCustomerUserRoleACLData::USER_ACCOUNT_1_2_ROLE_LOCAL,
                'indexResponseStatus' => 200,
                'gridResponseStatus' => 200,
                'data' => [
                    LoadCustomerUserRoleACLData::ROLE_FRONTEND_ADMINISTRATOR,
                    LoadCustomerUserRoleACLData::ROLE_FRONTEND_BUYER,
                    LoadCustomerUserRoleACLData::ROLE_DEEP,
                    LoadCustomerUserRoleACLData::ROLE_DEEP_VIEW_ONLY,
                    LoadCustomerUserRoleACLData::ROLE_LOCAL,
                    LoadCustomerUserRoleACLData::ROLE_LOCAL_VIEW_ONLY,
                    LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_2_USER_LOCAL,
                    LoadCustomerUserRoleACLData::ROLE_WITHOUT_ACCOUNT_1_USER_LOCAL,
                    LoadCustomerUserRoleACLData::ROLE_WITHOUT_ACCOUNT_1_USER_LOCAL_CANT_DELETED,
                    LoadCustomerUserRoleACLData::ROLE_WITH_ACCOUNT_1_2_USER_LOCAL_CANT_DELETED
                ],
            ],
        ];
    }
}
