<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData as OroLoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CustomerUserRoleControllerTest extends WebTestCase
{
    private const CUSTOMIZED_ROLE = 'Test customized role';
    private const ACCOUNT_ROLE = 'Test customer user role';
    private const ACCOUNT_UPDATED_ROLE = 'Updated test customer user role';

    private array $privileges = [
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

    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(OroLoadCustomerUserData::AUTH_USER, OroLoadCustomerUserData::AUTH_PW)
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures([
            LoadCustomers::class,
            LoadCustomerUserRoleData::class
        ]);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_role_create'));

        self::assertStringContainsString('frontend-customer-user-role-permission-grid', $crawler->html());
        self::assertStringContainsString('frontend-customer-customer-users-grid', $crawler->html());

        $form = $crawler->filter('[data-bottom-actions] button:contains(Create)')->form();
        $form['oro_customer_frontend_customer_user_role[label]'] = self::ACCOUNT_ROLE;
        $form['oro_customer_frontend_customer_user_role[privileges]'] = json_encode(
            $this->privileges,
            JSON_THROW_ON_ERROR
        );

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Customer User Role has been saved', $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_role_index'));
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);

        $response = $this->client->requestFrontendGrid('frontend-customer-customer-user-roles-grid');

        self::assertJsonResponseStatusCodeEquals($response, 200);
        self::assertStringContainsString(LoadCustomerUserRoleData::ROLE_WITH_ACCOUNT_USER, $response->getContent());
        self::assertStringContainsString(self::ACCOUNT_ROLE, $response->getContent());
    }

    /**
     * @depends testCreate
     */
    public function testUpdate(): int
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

        $token = $this->getCsrfToken('oro_customer_frontend_customer_user_role')->getValue();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), [
            'oro_customer_frontend_customer_user_role' => [
                '_token' => $token,
                'label' => self::ACCOUNT_UPDATED_ROLE,
                'appendUsers' => $this->getCurrentUser()->getId(),
                'privileges' => json_encode($this->privileges, JSON_THROW_ON_ERROR),
            ]
        ]);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        $content = $crawler->html();
        self::assertStringContainsString('Customer User Role has been saved', $content);

        $this->getEntityManager()->clear();

        /** @var CustomerUserRole $role */
        $role = $this->getUserRoleRepository()->find($id);

        self::assertNotNull($role);
        self::assertEquals(self::ACCOUNT_UPDATED_ROLE, $role->getLabel());
        self::assertNotEmpty($role->getRole());

        $user = $this->getCurrentUser();

        self::assertEquals($role, $user->getUserRole($role->getRole()));

        return $id;
    }

    /**
     * @depends testUpdate
     */
    public function testView(int $id)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_view', ['id' => $id])
        );

        self::assertResponseStatusCodeEquals($this->client->getResponse(), 200);

        $authUser = OroLoadCustomerUserData::AUTH_USER;
        $response = $this->client->requestFrontendGrid(
            'frontend-customer-customer-users-grid-view',
            [
                'frontend-customer-customer-users-grid-view[role]' => $id,
                'frontend-customer-customer-users-grid-view[_filter][email][value]' => $authUser
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        self::assertCount(1, $result['data']);
        $result = reset($result['data']);

        $currentUser = $this->getCurrentUser();
        self::assertEquals($currentUser->getId(), $result['id']);
        self::assertStringContainsString($currentUser->getFullName(), $result['fullName']);
        self::assertStringContainsString($currentUser->getEmail(), $result['email']);
        self::assertEquals(
            $currentUser->isEnabled() && $currentUser->isConfirmed() ? 'Active' : 'Inactive',
            trim($result['status'])
        );
    }

    /**
     * @depends testView
     */
    public function testUpdateFromPredefined()
    {
        $currentUser = $this->getCurrentUser();
        $currentUserRoles = $currentUser->getUserRoles();
        $oldRoleId = $this->getPredefinedRole()->getId();

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_update', ['id' => $oldRoleId])
        );

        $form = $crawler->selectButton('Save')->form();
        $token = $this->getCsrfToken('oro_customer_frontend_customer_user_role')->getValue();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), [
            'oro_customer_frontend_customer_user_role' => [
                '_token' => $token,
                'label' => self::CUSTOMIZED_ROLE,
                'appendUsers' => $currentUser->getId(),
                'privileges' => json_encode($this->privileges, JSON_THROW_ON_ERROR),
            ]
        ]);

        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);

        $content = $crawler->html();
        self::assertStringContainsString('Customer User Role has been saved', $content);

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
        self::assertNotEquals($newRoleId, $oldRoleId);

        /** @var CustomerUserRole $role */
        $role = $this->getUserRoleRepository()->find($newRoleId);

        self::assertNotNull($role);
        self::assertEquals(self::CUSTOMIZED_ROLE, $role->getLabel());
        self::assertNotEmpty($role->getRole());

        $user = $this->getCurrentUser();

        // Add new role
        self::assertCount(count($currentUserRoles) + 1, $user->getUserRoles());
        self::assertEquals($user->getUserRole($role->getRole()), $role);
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

        /** @var CustomerUserRole $role */
        $role = $this->getUserRoleRepository()->find($id);
        self::assertFalse($role->isPredefined());
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
        self::assertNotContainsEquals(
            $this->getReference(LoadCustomerUserRoleData::ROLE_NOT_SELF_MANAGED)->getId(),
            $visibleRoleIds
        );

        // visible not self managed role (self_managed = true and public = true)
        self::assertContainsEquals(
            $this->getReference(LoadCustomerUserRoleData::ROLE_SELF_MANAGED)->getId(),
            $visibleRoleIds
        );

        // invisible not public role (self_managed = true and public = false)
        self::assertNotContainsEquals(
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
        self::assertResponseStatusCodeEquals($this->client->getResponse(), 403);

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_update', ['id' => $notSelfManagedRole->getId()])
        );
        self::assertResponseStatusCodeEquals($this->client->getResponse(), 403);
    }

    public function testShouldNotAllowViewAndUpdateNotPublicRole()
    {
        $notPublicRole = $this->getReference(LoadCustomerUserRoleData::ROLE_NOT_PUBLIC);

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_view', ['id' => $notPublicRole->getId()])
        );
        self::assertResponseStatusCodeEquals($this->client->getResponse(), 403);

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_role_update', ['id' => $notPublicRole->getId()])
        );
        self::assertResponseStatusCodeEquals($this->client->getResponse(), 403);
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

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringNotContainsString('Marketing', $crawler->html());
        self::assertStringNotContainsString('Catalog', $crawler->html());
    }

    private function getPredefinedRole(): CustomerUserRole
    {
        return $this->getUserRoleRepository()
            ->findOneBy(['label' => LoadCustomerUserRoleData::ROLE_EMPTY]);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    private function getUserRepository(): EntityRepository
    {
        return $this->getEntityManager()->getRepository(CustomerUser::class);
    }

    private function getUserRoleRepository(): EntityRepository
    {
        return $this->getEntityManager()->getRepository(CustomerUserRole::class);
    }

    private function getCurrentUser(): CustomerUser
    {
        return $this->getUserRepository()->findOneBy(['username' => OroLoadCustomerUserData::AUTH_USER]);
    }
}
