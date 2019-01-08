<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Controller\EmailMessageAssertionTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadUserAndGuestWithSameUsername;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class CustomerUserControllerRegisterTest extends WebTestCase
{
    use EmailMessageAssertionTrait;

    const EMAIL = 'john.doe@example.com';
    const PASSWORD = '123456';

    protected function setUp()
    {
        $this->initClient();
        $this->client->useHashNavigation(true);

        $this->loadFixtures([LoadCustomerUserData::class]);
        $this->loadFixtures([LoadUserAndGuestWithSameUsername::class]);
    }

    /**
     * @dataProvider getInvalidData
     *
     * @param string $firstPassword
     * @param string $secondPassword
     * @param string $message
     */
    public function testInvalidRegister($firstPassword, $secondPassword, $message)
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_register'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Create An Account')->form();

        $submittedData = [
            'oro_customer_frontend_customer_user_register' => [
                '_token' => $form->get('oro_customer_frontend_customer_user_register[_token]')->getValue(),
                'companyName' => 'Test Company',
                'firstName' => 'Jim',
                'lastName' => 'Brown',
                'email' => self::EMAIL,
                'plainPassword' => [
                    'first' => $firstPassword,
                    'second' => $secondPassword
                ]
            ]
        ];

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form, $submittedData);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertEmpty($this->getCustomerUser(['email' => self::EMAIL]));
        $this->assertContains($message, $crawler->html());
    }

    /**
     * @return array
     */
    public function getInvalidData()
    {
        return [
            'mismatch passwords' => [
                'firstPassword' => 'plainPassword',
                'secondPassword' => 'plainPassword2',
                'errorMessage' => 'The password fields must match.'
            ],
            'low password complexity' => [
                'firstPassword' => '0',
                'secondPassword' => '0',
                'errorMessage' => 'The password must be at least 2 characters long'
            ]
        ];
    }

    public function testRegisterWithoutConfirmation()
    {
        $email = 'adam.smith@example.com';
        $configManager = $this->getContainer()->get('oro_config.manager');
        $configManager->set('oro_customer.confirmation_required', false);
        $configManager->flush();

        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_register'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->submitRegisterForm($crawler, $email);

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = $this->getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        $this->assertCount(1, $emailMessages);

        /** @var \Swift_Message $emailMessage */
        $emailMessage = array_shift($emailMessages);
        $this->assertWelcomeMessage($email, $emailMessage);
        $this->assertNotContains(
            'Please follow the link below to create a password for your new account.',
            $emailMessage->getBody()
        );

        $crawler = $this->client->followRedirect();
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $user = $this->getCustomerUser(['email' => $email]);
        $this->assertNotEmpty($user);
        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isConfirmed());
        $this->assertContains('Registration successful', $crawler->html());
    }

    public function testRegisterWithConfirmation()
    {
        $configManager = $this->getContainer()->get('oro_config.manager');
        $configManager->set('oro_customer.confirmation_required', true);
        $configManager->flush();

        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_register'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->submitRegisterForm($crawler, self::EMAIL);

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = $this->getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        /** @var \Swift_Message $message */
        $message = reset($emailMessages);

        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals(self::EMAIL, key($message->getTo()));
        $this->assertContains('Confirmation of account registration', $message->getSubject());

        $user = $this->getCustomerUser(['email' => self::EMAIL]);

        $applicationUrl = $configManager->get('oro_ui.application_url');
        $confirmMessage = 'Please follow this link to confirm your email address: <a href="'
            . $applicationUrl
            . htmlspecialchars($this->getUrl(
                'oro_customer_frontend_customer_user_confirmation',
                [
                    'username' => $user->getUsername(),
                    'token' => $user->getConfirmationToken()
                ]
            ))
            . '">Confirm</a>';
        $this->assertContains($confirmMessage, $message->getBody());

        $user = $this->getCustomerUser(['email' => self::EMAIL]);
        $this->assertNotEmpty($user);
        $this->assertTrue($user->isEnabled());
        $this->assertFalse($user->isConfirmed());

        $crawler = $this->client->followRedirect();
        $this->assertEquals(
            'Sign In',
            trim($crawler->filter('.login-form h2')->html())
        );
        $this->assertContains('Please check your email to complete registration', $crawler->html());

        $this->client->followRedirects(true);

        // Follow confirmation link
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_confirmation',
                [
                    'username' => $user->getUsername(),
                    'token' => $user->getConfirmationToken()
                ]
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Sign In', $crawler->html());

        $user = $this->getCustomerUser(['email' => self::EMAIL]);
        $this->assertNotEmpty($user);
        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isConfirmed());
    }

    /**
     * @depends testRegisterWithConfirmation
     */
    public function testRegisterExistingEmail()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_register'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Create An Account')->form();
        $submittedData = [
            'oro_customer_frontend_customer_user_register' => [
                '_token' => $form->get('oro_customer_frontend_customer_user_register[_token]')->getValue(),
                'companyName' => 'Test Company',
                'firstName' => 'Created',
                'lastName' => 'User',
                'email' => self::EMAIL,
                'plainPassword' => [
                    'first' => self::PASSWORD,
                    'second' => self::PASSWORD
                ]
            ]
        ];

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form, $submittedData);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('This email is already used.', $crawler->filter('.validation-failed')->html());
    }

    public function testResetPasswordWithLowPasswordComplexity()
    {
        $user = $this->getCustomerUser(['email' => LoadCustomerUserData::RESET_EMAIL]);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_password_reset',
                [
                    'token' => $user->getConfirmationToken(),
                    'username' => $user->getUsername()
                ]
            )
        );
        $form = $crawler->selectButton('Create')->form();

        $submittedData = [
            'oro_customer_customer_user_password_reset' => [
                '_token' => $form->get('oro_customer_customer_user_password_reset[_token]')->getValue(),
                'plainPassword' => [
                    'first' => '0',
                    'second' => '0'
                ]
            ]
        ];

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form, $submittedData);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('The password must be at least 2 characters long', $crawler->html());
    }

    /**
     * @depends testRegisterWithConfirmation
     */
    public function testResetPassword()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_user_security_login'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertEquals(
            'Sign In',
            trim($crawler->filter('.login-form h2')->html())
        );

        $forgotPasswordLink = $crawler->filter('a:contains("Forgot Your Password?")')->link();
        $crawler = $this->client->click($forgotPasswordLink);
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertEquals('Forgot Your Password?', $crawler->filter('h2')->html());

        $this->assertKnownEmail($crawler);

        // Follow reset password link
        $user = $this->getCustomerUser(['email' => self::EMAIL]);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_password_reset',
                [
                    'token' => $user->getConfirmationToken(),
                    'username' => $user->getUsername()
                ]
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertEquals('Create New Password', $crawler->filter('h2')->html());

        $form = $crawler->selectButton('Create')->form();

        $submittedData = [
            'oro_customer_customer_user_password_reset' => [
                '_token' => $form->get('oro_customer_customer_user_password_reset[_token]')->getValue(),
                'plainPassword' => [
                    'first' => '654321',
                    'second' => '654321'
                ]
            ]
        ];

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form, $submittedData);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertEquals(
            'Sign In',
            trim($crawler->filter('.login-form h2')->html())
        );
        $this->assertContains('Password was created successfully.', $crawler->html());
    }

    /**
     * @param array $criteria
     * @return CustomerUser
     */
    protected function getCustomerUser(array $criteria)
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getManagerForClass('OroCustomerBundle:CustomerUser')
            ->getRepository('OroCustomerBundle:CustomerUser')
            ->findOneBy($criteria);
    }

    /**
     * @param Crawler $crawler
     * @param string $email
     * @return Crawler
     */
    protected function submitRegisterForm(Crawler $crawler, $email)
    {
        $form = $crawler->selectButton('Create An Account')->form();
        $submittedData = [
            'oro_customer_frontend_customer_user_register' => [
                '_token' => $form->get('oro_customer_frontend_customer_user_register[_token]')->getValue(),
                'companyName' => 'Test Company',
                'firstName' => 'First Name',
                'lastName' => 'Last Name',
                'email' => $email,
                'plainPassword' => [
                    'first' => self::PASSWORD,
                    'second' => self::PASSWORD
                ]
            ]
        ];

        $this->client->followRedirects(false);

        return $this->client->submit($form, $submittedData);
    }

    /**
     * @param Crawler $crawler
     */
    protected function assertKnownEmail(Crawler $crawler)
    {
        $form = $crawler->selectButton('Request')->form();
        $submittedData = [
            'oro_customer_customer_user_password_request' => [
                '_token' => $form->get('oro_customer_customer_user_password_request[_token]')->getValue(),
                'email' => self::EMAIL
            ]
        ];

        $this->client->followRedirects(false);
        $this->client->submit($form, $submittedData);

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = $this->getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        /** @var \Swift_Message $message */
        $message = reset($emailMessages);

        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals(self::EMAIL, key($message->getTo()));
        $this->assertContains('Reset Account User Password', $message->getSubject());
        $this->assertContains('To reset your password - please visit', $message->getBody());
        $this->assertContains(self::EMAIL, $message->getBody());

        $user = $this->getCustomerUser(['email' => self::EMAIL]);
        $applicationUrl = $this->getContainer()->get('oro_config.manager')->get('oro_ui.application_url');

        $resetUrl = $applicationUrl
            . htmlspecialchars($this->getUrl(
                'oro_customer_frontend_customer_user_password_reset',
                [
                    'token' => $user->getConfirmationToken(),
                    'username' => $user->getUsername()
                ]
            ));

        $this->assertContains($resetUrl, $message->getBody());

        $crawler = $this->client->followRedirect();

        $this->assertEquals('Check Email', $crawler->filter('h2')->html());
    }

    public function testConfirmEmailSameUsernameForUserAndVisitor()
    {
        $user = $this->getCustomerUser(['email' => LoadUserAndGuestWithSameUsername::SAME_EMAIL, 'isGuest' => false]);
        $this->assertNotEmpty($user);
        $this->assertTrue($user->isEnabled());
        $this->assertFalse($user->isConfirmed());
        $this->assertNotNull($user->getConfirmationToken());

        $this->client->followRedirects(true);

        // Follow confirmation link
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_confirmation',
                [
                    'username' => $user->getUsername(),
                    'token' => $user->getConfirmationToken()
                ]
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $user = $this->getCustomerUser(['email' => LoadUserAndGuestWithSameUsername::SAME_EMAIL, 'isGuest' => false]);
        $this->assertNotEmpty($user);
        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isConfirmed());
    }
}
