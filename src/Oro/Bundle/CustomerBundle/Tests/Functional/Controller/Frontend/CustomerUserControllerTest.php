<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Controller\EmailMessageAssertionTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserACLData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
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
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                LoadCustomerUserACLData::class,
            ]
        );

        parent::setUp();
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param string $email
     * @param string $password
     * @param bool $isPasswordGenerate
     * @param bool $isSendEmail
     * @param int $emailsCount
     */
    public function testCreate($email, $password, $isPasswordGenerate, $isSendEmail, $emailsCount)
    {
        $this->loginUser(LoadCustomerUserACLData::USER_ACCOUNT_2_ROLE_DEEP);

        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_create'));
        $form = $crawler->selectButton('Save')->form();
        $form['oro_customer_frontend_customer_user[enabled]'] = true;
        $form['oro_customer_frontend_customer_user[namePrefix]'] = self::NAME_PREFIX;
        $form['oro_customer_frontend_customer_user[firstName]'] = self::FIRST_NAME;
        $form['oro_customer_frontend_customer_user[middleName]'] = self::MIDDLE_NAME;
        $form['oro_customer_frontend_customer_user[lastName]'] = self::LAST_NAME;
        $form['oro_customer_frontend_customer_user[nameSuffix]'] = self::NAME_SUFFIX;
        $form['oro_customer_frontend_customer_user[email]'] = $email;
        $form['oro_customer_frontend_customer_user[birthday]'] = date('Y-m-d');
        $form['oro_customer_frontend_customer_user[plainPassword][first]'] = $password;
        $form['oro_customer_frontend_customer_user[plainPassword][second]'] = $password;
        $form['oro_customer_frontend_customer_user[passwordGenerate]'] = $isPasswordGenerate;
        $form['oro_customer_frontend_customer_user[sendEmail]'] = $isSendEmail;

        /** @var ChoiceFormField[] $roleChoices */
        $roleChoices = $form['oro_customer_frontend_customer_user[userRoles]'];
        $this->assertCount(6, $roleChoices);
        $roleChoices[0]->tick();
        $this->client->submit($form);

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = $this->getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        $this->assertCount($emailsCount, $emailMessages);

        if ($isSendEmail) {
            /** @var \Swift_Message $emailMessage */
            $emailMessage = array_shift($emailMessages);
            $this->assertWelcomeMessage($email, $emailMessage);
            static::assertStringContainsString(
                'Please follow the link below to create a password for your new account.',
                $emailMessage->getBody()
            );
        }

        $crawler = $this->client->followRedirect();
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('Customer User has been saved', $crawler->html());
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
        $this->loginUser(LoadCustomerUserACLData::USER_ACCOUNT_2_ROLE_DEEP);

        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_create'));
        $form = $crawler->selectButton('Save')->form();
        $form['oro_customer_frontend_customer_user[plainPassword][first]'] = '0';
        $form['oro_customer_frontend_customer_user[plainPassword][second]'] = '0';
        $crawler = $this->client->submit($form);

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        static::assertStringContainsString('The password must be at least 2 characters long', $crawler->html());
    }

    /**
     * @dataProvider testCreatePermissionDeniedDataProvider
     * @group frontend-ACL
     * @param string $login
     * @param int $status
     */
    public function testCreatePermissionDenied($login, $status)
    {
        $this->loginUser($login);
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_create'));

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, $status);
    }

    /**
     * @return array
     */
    public function testCreatePermissionDeniedDataProvider()
    {
        return [
            'anonymous user' => [
                'login' => '',
                'status' => 401,
            ],
            'user without create permissions' => [
                'login' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY,
                'status' => 403,
            ],
        ];
    }

    /**
     * @depends testCreate
     */
    public function testIndex()
    {
        $this->markTestSkipped('Will be fixed in BB-12853');
        $this->loginUser(LoadCustomerUserACLData::USER_ACCOUNT_2_ROLE_DEEP);
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_index'));
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString(self::FIRST_NAME, $result->getContent());
        static::assertStringContainsString(self::LAST_NAME, $result->getContent());
        static::assertStringContainsString(self::EMAIL, $result->getContent());
    }

    /**
     * @depend testCreate
     * @return integer
     */
    public function testUpdate()
    {
        $this->markTestSkipped('Will be fixed in BB-12853');
        $this->loginUser(LoadCustomerUserACLData::USER_ACCOUNT_2_ROLE_DEEP);
        $response = $this->client->requestFrontendGrid(
            'frontend-customer-customer-user-grid',
            [
                'frontend-customer-customer-user-grid[_filter][firstName][value]' => self::FIRST_NAME,
                'frontend-customer-customer-user-grid[_filter][LastName][value]' => self::LAST_NAME,
                'frontend-customer-customer-user-grid[_filter][email][value]' => self::EMAIL,
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $id = $result['id'];

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_update', ['id' => $id])
        );

        $form = $crawler->selectButton('Save')->form();
        $form['oro_customer_frontend_customer_user[enabled]'] = false;
        $form['oro_customer_frontend_customer_user[namePrefix]'] = self::UPDATED_NAME_PREFIX;
        $form['oro_customer_frontend_customer_user[firstName]'] = self::UPDATED_FIRST_NAME;
        $form['oro_customer_frontend_customer_user[middleName]'] = self::UPDATED_MIDDLE_NAME;
        $form['oro_customer_frontend_customer_user[lastName]'] = self::UPDATED_LAST_NAME;
        $form['oro_customer_frontend_customer_user[nameSuffix]'] = self::UPDATED_NAME_SUFFIX;
        $form['oro_customer_frontend_customer_user[email]'] = self::UPDATED_EMAIL;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('Customer User has been saved', $crawler->html());

        return $id;
    }

    /**
     * @depends testUpdate
     * @param integer $id
     * @return integer
     */
    public function testView($id)
    {
        $this->loginUser(LoadCustomerUserACLData::USER_ACCOUNT_2_ROLE_DEEP);
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_view', ['id' => $id]));

        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $content = $result->getContent();

        static::assertStringContainsString(self::UPDATED_EMAIL, $content);

        return $id;
    }

    /**
     * @group frontend-ACL
     * @dataProvider ACLProvider
     *
     * @param string $route
     * @param string $resource
     * @param string $user
     * @param int $status
     */
    public function testACL($route, $resource, $user, $status)
    {
        $this->loginUser($user);
        /* @var CustomerUser $resource */
        $resource = $this->getReference($resource);

        $this->client->request(
            'GET',
            $this->getUrl(
                $route,
                ['id' => $resource->getId()]
            )
        );

        $response = $this->client->getResponse();
        static::assertHtmlResponseStatusCodeEquals($response, $status);
    }

    /**
     * @return array
     */
    public function ACLProvider()
    {
        return [
            'VIEW (anonymous user)' => [
                'route' => 'oro_customer_frontend_customer_user_view',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => '',
                'status' => 401,
            ],
            'VIEW (user from another customer)' => [
                'route' => 'oro_customer_frontend_customer_user_view',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_2_ROLE_LOCAL,
                'status' => 403,
            ],
            'VIEW (user from parent customer : DEEP_VIEW_ONLY)' => [
                'route' => 'oro_customer_frontend_customer_user_view',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY,
                'status' => 200,
            ],
            'VIEW (user from parent customer : LOCAL)' => [
                'route' => 'oro_customer_frontend_customer_user_view',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'status' => 403,
            ],
            'VIEW (user from same customer : LOCAL)' => [
                'route' => 'oro_customer_frontend_customer_user_view',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_DEEP,
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'status' => 200,
            ],
            'UPDATE (anonymous user)' => [
                'route' => 'oro_customer_frontend_customer_user_update',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => '',
                'status' => 401,
            ],
            'UPDATE (user from another customer)' => [
                'route' => 'oro_customer_frontend_customer_user_update',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_2_ROLE_LOCAL,
                'status' => 403,
            ],
            'UPDATE (user from parent customer : DEEP)' => [
                'route' => 'oro_customer_frontend_customer_user_update',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'status' => 200,
            ],
            'UPDATE (user from parent customer : LOCAL_VIEW_ONLY)' => [
                'route' => 'oro_customer_frontend_customer_user_update',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY,
                'status' => 403,
            ],
            'UPDATE (user from same customer : LOCAL_VIEW_ONLY)' => [
                'route' => 'oro_customer_frontend_customer_user_update',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL_VIEW_ONLY,
                'status' => 403,
            ],
            'UPDATE (user from same customer : LOCAL)' => [
                'route' => 'oro_customer_frontend_customer_user_update',
                'resource' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'status' => 200,
            ],
        ];
    }

    /**
     * @group frontend-ACL
     * @dataProvider gridACLProvider
     *
     * @param string $user
     * @param string $indexResponseStatus
     * @param string $gridResponseStatus
     * @param array $data
     */
    public function testGridACL($user, $indexResponseStatus, $gridResponseStatus, array $data = [])
    {
        $this->markTestSkipped('Will be fixed in BB-12853');
        $this->loginUser($user);
        $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_index'));
        $this->assertSame($indexResponseStatus, $this->client->getResponse()->getStatusCode());
        $response = $this->client->requestGrid(
            [
                'gridName' => 'frontend-customer-customer-user-grid',
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

    /**
     * @return array
     */
    public function gridACLProvider()
    {
        return [
            'NOT AUTHORISED' => [
                'user' => '',
                'indexResponseStatus' => 401,
                'gridResponseStatus' => 403,
                'data' => [],
            ],
            'DEEP: all siblings and children' => [
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP,
                'indexResponseStatus' => 200,
                'gridResponseStatus' => 200,
                'data' => [
                    LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                    LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP,
                    LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL_VIEW_ONLY,
                    LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY,
                    LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_DEEP,
                    LoadCustomerUserACLData::USER_ACCOUNT_1_1_ROLE_LOCAL,
                    LoadCustomerUserACLData::USER_ACCOUNT_1_2_ROLE_DEEP,
                    LoadCustomerUserACLData::USER_ACCOUNT_1_2_ROLE_LOCAL,
                ],
            ],
            'LOCAL: all siblings' => [
                'user' => LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                'indexResponseStatus' => 200,
                'gridResponseStatus' => 200,
                'data' => [
                    LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL,
                    LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP,
                    LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL_VIEW_ONLY,
                    LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_DEEP_VIEW_ONLY,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEmail()
    {
        return self::EMAIL;
    }
}
