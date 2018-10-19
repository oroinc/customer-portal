<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData as OroLoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CustomerUserRoleControllerTest extends WebTestCase
{
    const PREDEFINED_ROLE = 'Test predefined role';
    const CUSTOMIZED_ROLE = 'Test customized role';
    const ACCOUNT_ROLE = 'Test customer user role';
    const ACCOUNT_UPDATED_ROLE = 'Updated test customer user role';

    /**
     * @var array
     */
    protected $privileges = [
        'action' => [
            0 => [
                'identity' => [
                    'id' => 'action:oro_order_address_billing_allow_manual',
                    'name' => 'oro.order.security.permission.address_billing_allow_manual',
                ],
                'permissions' => [],
            ],
        ],
        'entity' => [
            0 => [
                'identity' => [
                    'id' => 'entity:Oro\Bundle\CustomerBundle\Entity\Customer',
                    'name' => 'oro.customer.customer.entity_label',
                ],
                'permissions' => [],
            ],
        ],
    ];

    /**
     * @var CustomerUser
     */
    protected $currentUser;

    /**
     * @var CustomerUserRole
     */
    protected $predefinedRole;

    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(OroLoadCustomerUserData::AUTH_USER, OroLoadCustomerUserData::AUTH_PW)
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers',
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData'
            ]
        );

        $this->currentUser = $this->getCurrentUser();
        $this->predefinedRole = $this->getPredefinedRole();
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_role_create'));

        $this->assertContains('frontend-customer-user-role-permission-grid', $crawler->html());
        $this->assertContains('frontend-customer-customer-users-grid', $crawler->html());

        $form = $crawler->selectButton('Create')->form();
        $form['oro_customer_frontend_customer_user_role[label]'] = self::ACCOUNT_ROLE;
        $form['oro_customer_frontend_customer_user_role[privileges]'] = json_encode($this->privileges);

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Customer User Role has been saved', $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_role_index'));
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $response = $this->client->requestFrontendGrid('frontend-customer-customer-user-roles-grid');

        $this->assertJsonResponseStatusCodeEquals($response, 200);
        $this->assertContains(LoadCustomerUserRoleData::ROLE_WITH_ACCOUNT_USER, $response->getContent());
        $this->assertContains(self::ACCOUNT_ROLE, $response->getContent());
    }

    /**
     * @depends testCreate
     * @return int
     */
    public function testUpdate()
    {
        $response = $this->client->requestFrontendGrid(
            'frontend-customer-customer-user-roles-grid',
            [
                'frontend-customer-customer-user-roles-grid[_filter][label][value]' => self::ACCOUNT_ROLE
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $id = $result['id'];

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_update', ['id' => $id])
        );

        $form = $crawler->selectButton('Save')->form();

        $token = $this->getContainer()->get('security.csrf.token_manager')
            ->getToken('oro_customer_frontend_customer_user_role')->getValue();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), [
            'oro_customer_frontend_customer_user_role' => [
                '_token' => $token,
                'label' => self::ACCOUNT_UPDATED_ROLE,
                'appendUsers' => $this->currentUser->getId(),
                'privileges' => json_encode($this->privileges),
            ]
        ]);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $content = $crawler->html();
        $this->assertContains('Customer User Role has been saved', $content);

        $this->getObjectManager()->clear();

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUserRole $role */
        $role = $this->getUserRoleRepository()->find($id);

        $this->assertNotNull($role);
        $this->assertEquals(self::ACCOUNT_UPDATED_ROLE, $role->getLabel());
        $this->assertNotEmpty($role->getRole());

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUser $user */
        $user = $this->getCurrentUser();

        $this->assertEquals($role, $user->getRole($role->getRole()));

        return $id;
    }

    /**
     * @depends testUpdate
     * @param $id
     */
    public function testView($id)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_view', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 200);

        $authUser = OroLoadCustomerUserData::AUTH_USER;
        $response = $this->client->requestFrontendGrid(
            'frontend-customer-customer-users-grid-view',
            [
                'frontend-customer-customer-users-grid-view[role]' => $id,
                'frontend-customer-customer-users-grid-view[_filter][email][value]' => $authUser
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(1, $result['data']);
        $result = reset($result['data']);

        $this->assertEquals($this->currentUser->getId(), $result['id']);
        $this->assertContains($this->currentUser->getFullName(), $result['fullName']);
        $this->assertContains($this->currentUser->getEmail(), $result['email']);
        $this->assertEquals(
            $this->currentUser->isEnabled() && $this->currentUser->isConfirmed() ? 'Active' : 'Inactive',
            trim($result['status'])
        );
    }

    /**
     * @depends testView
     */
    public function testUpdateFromPredefined()
    {
        $currentUserRoles = $this->currentUser->getRoles();
        $oldRoleId = $this->predefinedRole->getId();

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_update', ['id' => $oldRoleId])
        );

        $form = $crawler->selectButton('Save')->form();
        $token = $this->getContainer()->get('security.csrf.token_manager')
            ->getToken('oro_customer_frontend_customer_user_role')->getValue();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), [
            'oro_customer_frontend_customer_user_role' => [
                '_token' => $token,
                'label' => self::CUSTOMIZED_ROLE,
                'appendUsers' => $this->currentUser->getId(),
                'privileges' => json_encode($this->privileges),
            ]
        ]);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $content = $crawler->html();
        $this->assertContains('Customer User Role has been saved', $content);

        // Find id of new role
        $response = $this->client->requestFrontendGrid(
            'frontend-customer-customer-user-roles-grid',
            [
                'frontend-customer-customer-user-roles-grid[_filter][label][value]' => self::CUSTOMIZED_ROLE
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $newRoleId = $result['id'];
        $this->assertNotEquals($newRoleId, $oldRoleId);

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUserRole $role */
        $role = $this->getUserRoleRepository()->find($newRoleId);

        $this->assertNotNull($role);
        $this->assertEquals(self::CUSTOMIZED_ROLE, $role->getLabel());
        $this->assertNotEmpty($role->getRole());

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUser $user */
        $user = $this->getCurrentUser();

        // Add new role
        $this->assertCount(count($currentUserRoles) + 1, $user->getRoles());
        $this->assertEquals($user->getRole($role->getRole()), $role);
    }

    /**
     * @depends testUpdateFromPredefined
     */
    public function testIndexFromPredefined()
    {
        $response = $this->client->requestFrontendGrid(
            'frontend-customer-customer-user-roles-grid',
            [
                'frontend-customer-customer-user-roles-grid[_filter][label][value]' => self::CUSTOMIZED_ROLE
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $id = $result['id'];

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUserRole $role */
        $role = $this->getUserRoleRepository()->find($id);
        $this->assertFalse($role->isPredefined());
    }

    public function testDisplaySelfManagedPublicRoles()
    {
        $response = $this->client->requestFrontendGrid('frontend-customer-customer-user-roles-grid');
        $result = $this->getJsonResponseContent($response, 200);

        $visibleRoleIds = array_map(
            function (array $row) {
                return $row['id'];
            },
            $result['data']
        );

        // invisible role not self managed role (self_managed = false and public = true)
        $this->assertNotContains(
            $this->getReference(LoadCustomerUserRoleData::ROLE_NOT_SELF_MANAGED)->getId(),
            $visibleRoleIds
        );

        // visible not self managed role (self_managed = true and public = true)
        $this->assertContains(
            $this->getReference(LoadCustomerUserRoleData::ROLE_SELF_MANAGED)->getId(),
            $visibleRoleIds
        );

        // invisible not public role (self_managed = true and public = false)
        $this->assertNotContains(
            $this->getReference(LoadCustomerUserRoleData::ROLE_NOT_PUBLIC)->getId(),
            $visibleRoleIds
        );
    }

    public function testShouldNotAllowViewAndUpdateNotSelfManagedRole()
    {
        $notSelfManagedRole = $this->getReference(LoadCustomerUserRoleData::ROLE_NOT_SELF_MANAGED);

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_view', ['id' => $notSelfManagedRole->getId()])
        );
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 403);

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_update', ['id' => $notSelfManagedRole->getId()])
        );
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 403);
    }

    public function testShouldNotAllowViewAndUpdateNotPublicRole()
    {
        $notPublicRole = $this->getReference(LoadCustomerUserRoleData::ROLE_NOT_PUBLIC);

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_view', ['id' => $notPublicRole->getId()])
        );
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 403);

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_update', ['id' => $notPublicRole->getId()])
        );
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 403);
    }

    public function testViewNotContainsTabsWithEmptyPermissions()
    {
        /** @var CustomerUserRole[] $roles */
        $roles = $this->getUserRoleRepository()->findBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $role = array_shift($roles);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_view', ['id' => $role->getId()])
        );
        $result = $this->client->getResponse();

        static::assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertNotContains('Marketing', $crawler->html());
        static::assertNotContains('Catalog', $crawler->html());
    }

    /**
     * @return CustomerUserRole
     */
    protected function getPredefinedRole()
    {
        return $this->getUserRoleRepository()
            ->findOneBy(['label' => LoadCustomerUserRoleData::ROLE_EMPTY]);
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getUserRepository()
    {
        return $this->getObjectManager()->getRepository('OroCustomerBundle:CustomerUser');
    }

    /**
     * @return \Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository
     */
    protected function getCustomerRepository()
    {
        return $this->getObjectManager()->getRepository('OroCustomerBundle:Customer');
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getUserRoleRepository()
    {
        return $this->getObjectManager()->getRepository('OroCustomerBundle:CustomerUserRole');
    }

    /**
     * @return CustomerUser
     */
    protected function getCurrentUser()
    {
        return $this->getUserRepository()->findOneBy(['username' => OroLoadCustomerUserData::AUTH_USER]);
    }
}
