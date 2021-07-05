<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserRoleControllerTest extends WebTestCase
{
    const TEST_ROLE = 'Test customer user role';
    const UPDATED_TEST_ROLE = 'Updated test customer user role';

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
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers',
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData'
            ]
        );
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_user_role_create'));

        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_customer_customer_user_role[label]'] = self::TEST_ROLE;
        $form['oro_customer_customer_user_role[privileges]'] = json_encode($this->privileges);

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
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_user_role_index'));
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('customer-customer-user-roles-grid', $crawler->html());
        self::assertStringContainsString(self::TEST_ROLE, $result->getContent());
    }

    /**
     * @depend testCreate
     * @return int
     */
    public function testUpdate()
    {
        /** @var CustomerUserRole $role = */
        $role = self::getContainer()->get('doctrine')
            ->getManagerForClass('OroCustomerBundle:CustomerUserRole')
            ->getRepository('OroCustomerBundle:CustomerUserRole')
            ->findOneBy(['label' => self::TEST_ROLE]);
        $id = $role->getId();

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_user_role_update', ['id' => $id])
        );

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUser $customerUser */
        $customerUser = $this->getUserRepository()->findOneBy(['email' => LoadCustomerUserData::EMAIL]);
        $customer = $this->getCustomerRepository()->findOneBy(['name' => 'customer.orphan']);
        $customerUser->setCustomer($customer);
        $this->getObjectManager()->flush();

        self::assertNotNull($customerUser);
        self::assertStringContainsString('Add note', $crawler->html());

        $form = $crawler->selectButton('Save and Close')->form();

        $token = self::getContainer()->get('security.csrf.token_manager')
            ->getToken('oro_customer_customer_user_role')->getValue();
        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), [
            'oro_customer_customer_user_role' => [
                '_token' => $token,
                'label' => self::UPDATED_TEST_ROLE,
                'selfManaged' => true,
                'customer' => $customer->getId(),
                'appendUsers' => $customerUser->getId(),
                'privileges' => json_encode($this->privileges),
            ]
        ]);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        $content = $crawler->html();
        self::assertStringContainsString('Customer User Role has been saved', $content);

        $this->getObjectManager()->clear();

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUserRole $role */
        $role = $this->getUserRoleRepository()->find($id);

        self::assertNotNull($role);
        self::assertEquals(self::UPDATED_TEST_ROLE, $role->getLabel());
        self::assertNotEmpty($role->getRole());

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUser $user */
        $user = $this->getUserRepository()->findOneBy(['email' => LoadCustomerUserData::EMAIL]);

        self::assertNotNull($user);
        self::assertEquals($user->getUserRole($role->getRole()), $role);

        self::assertTrue($role->isSelfManaged());

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
            $this->getUrl('oro_customer_customer_user_role_view', ['id' => $id])
        );

        $response = $this->client->getResponse();
        self::assertResponseStatusCodeEquals($response, 200);

        self::assertEquals(8, substr_count($response->getContent(), 'shipping address'));
        self::assertStringContainsString('Share data view', $response->getContent());
        self::assertStringNotContainsString('Access system information', $response->getContent());

        // Check datagrid
        $response = $this->client->requestGrid(
            'customer-customer-users-grid-view',
            [
                'customer-customer-users-grid-view[role]' => $id,
                'customer-customer-users-grid-view[_filter][email][value]' => LoadCustomerUserData::EMAIL
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        self::assertCount(1, $result['data']);

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getUserRepository()->findOneBy(['email' => LoadCustomerUserData::EMAIL]);
        $result = reset($result['data']);

        self::assertEquals($customerUser->getId(), $result['id']);
        self::assertEquals($customerUser->getFirstName(), $result['firstName']);
        self::assertEquals($customerUser->getLastName(), $result['lastName']);
        self::assertEquals($customerUser->getEmail(), $result['email']);
    }

    /**
     * @return \Doctrine\Persistence\ObjectManager
     */
    protected function getObjectManager()
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return \Doctrine\Persistence\ObjectRepository
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
     * @return \Doctrine\Persistence\ObjectRepository
     */
    protected function getUserRoleRepository()
    {
        return $this->getObjectManager()->getRepository('OroCustomerBundle:CustomerUserRole');
    }
}
