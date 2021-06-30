<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadCustomerUserRoles;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadUserData;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CustomerUserControllerTest extends WebTestCase
{
    use EmailMessageAssertionTrait;

    const NAME_PREFIX = 'NamePrefix';
    const MIDDLE_NAME = 'MiddleName';
    const NAME_SUFFIX = 'NameSuffix';
    const EMAIL = 'first@example.com';
    const FIRST_NAME = 'John';
    const LAST_NAME = 'Doe';

    const UPDATED_NAME_PREFIX = 'UNamePrefix';
    const UPDATED_FIRST_NAME = 'UFirstName';
    const UPDATED_MIDDLE_NAME = 'UMiddleName';
    const UPDATED_LAST_NAME = 'UpdLastName';
    const UPDATED_NAME_SUFFIX = 'UNameSuffix';
    const UPDATED_EMAIL = 'updated@example.com';

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                LoadCustomers::class,
                LoadCustomerUserRoleData::class,
                LoadUserData::class
            ]
        );
    }

    /**
     * @dataProvider createDataProvider
     * @param string $email
     * @param string $password
     * @param bool $isPasswordGenerate
     * @param bool $isSendEmail
     * @param int $emailsCount
     */
    public function testCreate($email, $password, $isPasswordGenerate, $isSendEmail, $emailsCount)
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_user_create'));
        self::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        /** @var Customer $customer */
        $customer = self::getContainer()
            ->get('doctrine')
            ->getManagerForClass(Customer::class)
            ->getRepository(Customer::class)
            ->findOneBy([]);

        /** @var CustomerUserRole $role */
        $role = self::getContainer()
            ->get('doctrine')
            ->getManagerForClass(CustomerUserRole::class)
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(
                ['role' => CustomerUserRole::PREFIX_ROLE . LoadCustomerUserRoles::ADMINISTRATOR]
            );

        self::assertNotNull($customer);
        self::assertNotNull($role);

        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_customer_customer_user[enabled]'] = true;
        $form['oro_customer_customer_user[namePrefix]'] = self::NAME_PREFIX;
        $form['oro_customer_customer_user[firstName]'] = self::FIRST_NAME;
        $form['oro_customer_customer_user[middleName]'] = self::MIDDLE_NAME;
        $form['oro_customer_customer_user[lastName]'] = self::LAST_NAME;
        $form['oro_customer_customer_user[nameSuffix]'] = self::NAME_SUFFIX;
        $form['oro_customer_customer_user[email]'] = $email;
        $form['oro_customer_customer_user[birthday]'] = date('Y-m-d');
        $form['oro_customer_customer_user[plainPassword][first]'] = $password;
        $form['oro_customer_customer_user[plainPassword][second]'] = $password;
        $form['oro_customer_customer_user[customer]'] = $customer->getId();
        $form['oro_customer_customer_user[passwordGenerate]'] = $isPasswordGenerate;
        $form['oro_customer_customer_user[sendEmail]'] = $isSendEmail;
        $form['oro_customer_customer_user[userRoles][0]']->tick();
        $form['oro_customer_customer_user[salesRepresentatives]'] = implode(',', [
            $this->getReference(LoadUserData::USER1)->getId(),
            $this->getReference(LoadUserData::USER2)->getId()
        ]);

        $this->client->submit($form);

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = self::getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        self::assertCount($emailsCount, $emailMessages);

        if ($isSendEmail) {
            /** @var \Swift_Message $emailMessage */
            $emailMessage = array_shift($emailMessages);
            self::assertWelcomeMessage($email, $emailMessage);
            self::assertStringContainsString(
                'Please follow the link below to create a password for your new account.',
                $emailMessage->getBody()
            );
        }

        $crawler = $this->client->followRedirect();
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Customer User has been saved', $crawler->html());
        self::assertStringContainsString(
            $this->getReference(LoadUserData::USER1)->getFullName(),
            $result->getContent()
        );
        self::assertStringContainsString(
            $this->getReference(LoadUserData::USER2)->getFullName(),
            $result->getContent()
        );
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            'simple create' => [
                'email' => $this->getEmail(),
                'password' => '123456',
                'isPasswordGenerate' => false,
                'isSendEmail' => false,
                'emailsCount' => 0
            ],
            'create with email and without password generator' => [
                'email' => 'second@example.com',
                'password' => '123456',
                'isPasswordGenerate' => false,
                'isSendEmail' => true,
                'emailsCount' => 1
            ],
            'create with email and password generator' => [
                'email' => 'third@example.com',
                'password' => '',
                'isPasswordGenerate' => true,
                'isSendEmail' => true,
                'emailsCount' => 1
            ]
        ];
    }

    public function testCreateWithLowPasswordComplexity()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_user_create'));
        self::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_customer_customer_user[plainPassword][first]'] = '0';
        $form['oro_customer_customer_user[plainPassword][second]'] = '0';

        $crawler = $this->client->submit($form);

        self::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        self::assertStringContainsString('The password must be at least 2 characters long', $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_user_index'));
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('customer-customer-user-grid', $crawler->html());
        self::assertStringContainsString(self::FIRST_NAME, $result->getContent());
        self::assertStringContainsString(self::LAST_NAME, $result->getContent());
        self::assertStringContainsString(self::EMAIL, $result->getContent());
        self::assertStringContainsString('Export', $result->getContent());
    }

    /**
     * @depends testCreate
     * @return integer
     */
    public function testUpdate()
    {
        /** @var CustomerUser $customer */
        $customerUser = self::getContainer()->get('doctrine')
            ->getManagerForClass(CustomerUser::class)
            ->getRepository(CustomerUser::class)
            ->findOneBy([
                'email' => self::EMAIL,
                'firstName' => self::FIRST_NAME,
                'lastName' => self::LAST_NAME
            ]);
        $id = $customerUser->getId();

        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_user_update', ['id' => $id]));

        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_customer_customer_user[enabled]'] = false;
        $form['oro_customer_customer_user[namePrefix]'] = self::UPDATED_NAME_PREFIX;
        $form['oro_customer_customer_user[firstName]'] = self::UPDATED_FIRST_NAME;
        $form['oro_customer_customer_user[middleName]'] = self::UPDATED_MIDDLE_NAME;
        $form['oro_customer_customer_user[lastName]'] = self::UPDATED_LAST_NAME;
        $form['oro_customer_customer_user[nameSuffix]'] = self::UPDATED_NAME_SUFFIX;
        $form['oro_customer_customer_user[email]'] = self::UPDATED_EMAIL;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Customer User has been saved', $crawler->html());

        return $id;
    }

    /**
     * @depends testUpdate
     * @param integer $id
     * @return integer
     */
    public function testView($id)
    {
        $this->client->request('GET', $this->getUrl('oro_customer_customer_user_view', ['id' => $id]));

        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);
        $content = $result->getContent();

        self::assertStringContainsString(
            sprintf('%s - Customer Users - Customers', self::UPDATED_EMAIL),
            $content
        );

        self::assertStringContainsString('Add attachment', $content);
        self::assertStringContainsString('Add note', $content);
        self::assertStringContainsString('Send email', $content);
        self::assertStringContainsString('Add Event', $content);
        self::assertStringContainsString('Address Book', $content);

        return $id;
    }

    /**
     * @depends testUpdate
     * @param integer $id
     */
    public function testInfo($id)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_user_info', ['id' => $id]),
            ['_widgetContainer' => 'dialog']
        );

        /** @var CustomerUser $user */
        $user = self::getContainer()->get('doctrine')
            ->getManagerForClass(CustomerUser::class)
            ->getRepository(CustomerUser::class)
            ->find($id);
        self::assertNotNull($user);

        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUserRole $role */
        $roles = $user->getUserRoles();
        $role = reset($roles);
        self::assertNotNull($role);

        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString(self::UPDATED_FIRST_NAME, $result->getContent());
        self::assertStringContainsString(self::UPDATED_LAST_NAME, $result->getContent());
        self::assertStringContainsString(self::UPDATED_EMAIL, $result->getContent());
        self::assertStringContainsString($user->getCustomer()->getName(), $result->getContent());
        self::assertStringContainsString($role->getLabel(), $result->getContent());
    }

    public function testGetRolesWithCustomerAction()
    {
        $manager = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUserRole::class);

        $foreignCustomer = $this->createCustomer('Foreign customer');
        $foreignRole = $this->createCustomerUserRole('Custom foreign role');
        $foreignRole->setCustomer($foreignCustomer);

        $expectedRoles[] = $this->createCustomerUserRole('Predefined test role');
        $notExpectedRoles[] = $foreignRole;
        $manager->flush();

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_user_roles'),
            ['_widgetContainer' => 'widget']
        );
        $response = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($response, 200);
        self::assertRoles($expectedRoles, $notExpectedRoles, $response->getContent());

        // With customer parameter
        $expectedRoles = $notExpectedRoles = [];
        $expectedRoles[] = $foreignRole;

        $this->client->request(
            'GET',
            $this->getUrl('oro_customer_customer_user_roles', ['customerId' => $foreignCustomer->getId()])
        );

        $response = $this->client->getResponse();

        self::assertRoles($expectedRoles, $notExpectedRoles, $response->getContent());
    }

    public function testGetRolesWithUserAction()
    {
        $manager = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUserRole::class);

        $foreignCustomer = $this->createCustomer('User foreign customer');
        $notExpectedRoles[] = $foreignRole = $this->createCustomerUserRole('Custom user foreign role');
        $foreignRole->setCustomer($foreignCustomer);

        $userCustomer = $this->createCustomer('User customer');
        $expectedRoles[] = $userRole = $this->createCustomerUserRole('Custom user role');
        $userRole->setCustomer($userCustomer);

        $customerUser = $this->createCustomerUser('test@example.com');
        $customerUser->setCustomer($userCustomer);
        $customerUser->addUserRole($userRole);

        $expectedRoles[] = $predefinedRole = $this->createCustomerUserRole('User predefined role');
        $customerUser->addUserRole($predefinedRole);

        $manager->flush();

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_customer_user_roles',
                [
                    'customerUserId' => $customerUser->getId(),
                    'customerId' => $userCustomer->getId(),
                ]
            ),
            ['_widgetContainer' => 'widget']
        );

        $response = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($response, 200);
        self::assertRoles($expectedRoles, $notExpectedRoles, $response->getContent(), $customerUser);

        // Without customer parameter
        $expectedRoles = $notExpectedRoles = [];
        $notExpectedRoles[] = $userRole;
        $expectedRoles[] = $predefinedRole;

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_customer_user_roles',
                [
                    'customerUserId' => $customerUser->getId(),
                ]
            ),
            ['_widgetContainer' => 'widget']
        );

        $response = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($response, 200);
        self::assertRoles($expectedRoles, $notExpectedRoles, $response->getContent(), $customerUser);


        //with predefined error
        $errorMessage = 'Test error message';
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_customer_user_roles',
                [
                    'customerUserId' => $customerUser->getId(),
                    'error' => $errorMessage
                ]
            ),
            ['_widgetContainer' => 'widget']
        );

        $response = $this->client->getResponse();
        self::assertStringContainsString($errorMessage, $response->getContent());
    }

    /**
     * @param string $name
     * @return Customer
     */
    protected function createCustomer($name)
    {
        $customer = new Customer();
        $customer->setName($name);
        $customer->setOrganization($this->getDefaultOrganization());
        self::getContainer()->get('doctrine')
            ->getManagerForClass(Customer::class)
            ->persist($customer);

        return $customer;
    }

    /**
     * @param string $name
     * @return CustomerUserRole
     */
    protected function createCustomerUserRole($name)
    {
        $role = new CustomerUserRole($name);
        $role->setLabel($name);
        $role->setOrganization($this->getDefaultOrganization());
        self::getContainer()->get('doctrine')
            ->getManagerForClass(CustomerUserRole::class)
            ->persist($role);

        return $role;
    }

    /**
     * @param string $email
     * @return CustomerUser
     */
    protected function createCustomerUser($email)
    {
        $customerUser = new CustomerUser();
        $customerUser->setEmail($email);
        $customerUser->setPassword('password');
        $customerUser->setOrganization($this->getDefaultOrganization());
        self::getContainer()->get('doctrine')
            ->getManagerForClass(CustomerUser::class)
            ->persist($customerUser);

        return $customerUser;
    }

    /**
     * @return Organization
     */
    protected function getDefaultOrganization()
    {
        return self::getContainer()->get('doctrine')
            ->getManagerForClass(Organization::class)
            ->getRepository(Organization::class)
            ->findOneBy([]);
    }

    /**
     * @param CustomerUserRole[] $expectedRoles
     * @param CustomerUserRole[] $notExpectedRoles
     * @param string $content
     * @param CustomerUser|null $customerUser
     */
    protected function assertRoles(
        array $expectedRoles,
        array $notExpectedRoles,
        $content,
        CustomerUser $customerUser = null
    ) {
        $shouldBeChecked = 0;
        /** @var CustomerUserRole $expectedRole */
        foreach ($expectedRoles as $expectedRole) {
            self::assertStringContainsString($expectedRole->getLabel(), $content);
            if ($customerUser && $customerUser->hasRole($expectedRole)) {
                $shouldBeChecked++;
            }
        }
        self::assertEquals($shouldBeChecked, substr_count($content, 'checked="checked"'));

        /** @var CustomerUserRole $notExpectedRole */
        foreach ($notExpectedRoles as $notExpectedRole) {
            self::assertStringNotContainsString($notExpectedRole->getLabel(), $content);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getEmail()
    {
        return self::EMAIL;
    }
}
