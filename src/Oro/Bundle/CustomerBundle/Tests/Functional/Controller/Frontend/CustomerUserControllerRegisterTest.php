<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Controller\EmailMessageAssertionTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadUserAndGuestWithSameUsername;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mime\Email as SymfonyEmail;

class CustomerUserControllerRegisterTest extends WebTestCase
{
    use EmailMessageAssertionTrait;

    private const EMAIL = 'john.doe@example.com';
    private const PASSWORD = '123456';

    protected function setUp(): void
    {
        $this->initClient();
        $this->client->useHashNavigation(true);

        $this->loadFixtures([LoadCustomerUserData::class]);
        $this->loadFixtures([LoadUserAndGuestWithSameUsername::class]);
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testInvalidRegister(string $firstPassword, string $secondPassword, string $message): void
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_register'));
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);

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
                    'second' => $secondPassword,
                ],
            ],
        ];

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form, $submittedData);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertEmpty($this->getCustomerUser(['email' => self::EMAIL]));
        self::assertStringContainsString($message, $crawler->html());
    }

    public function getInvalidData(): array
    {
        return [
            'mismatch passwords' => [
                'firstPassword' => 'plainPassword',
                'secondPassword' => 'plainPassword2',
                'errorMessage' => 'The password fields must match.',
            ],
            'low password complexity' => [
                'firstPassword' => '0',
                'secondPassword' => '0',
                'errorMessage' => 'The password must be at least 2 characters long',
            ],
        ];
    }

    public function testRegisterWithoutConfirmation(): void
    {
        $email = 'adam.smith@example.com';
        $configManager = self::getConfigManager();
        $configManager->set('oro_customer.confirmation_required', false);
        $configManager->flush();

        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_register'));
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);

        $this->submitRegisterForm($crawler, $email);

        $emailMessages = self::getMailerMessages();
        self::assertCount(1, $emailMessages);

        /** @var SymfonyEmail $emailMessage */
        $emailMessage = array_shift($emailMessages);

        $this->assertWelcomeMessage($email, $emailMessage);
        self::assertStringNotContainsString(
            'Please follow the link below to create a password for your new account.',
            $emailMessage->getHtmlBody()
        );

        $crawler = $this->client->followRedirect();
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);

        $user = $this->getCustomerUser(['email' => $email]);
        self::assertNotEmpty($user);
        self::assertTrue($user->isEnabled());
        self::assertTrue($user->isConfirmed());
        self::assertStringContainsString('Registration successful', $crawler->html());
    }

    public function testRegisterWithConfirmation(): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_customer.confirmation_required', true);
        $configManager->flush();

        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_register'));
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);

        $this->submitRegisterForm($crawler, self::EMAIL);

        $emailMessages = self::getMailerMessages();
        self::assertCount(1, $emailMessages);

        /** @var SymfonyEmail $emailMessage */
        $emailMessage = array_shift($emailMessages);

        self::assertInstanceOf(SymfonyEmail::class, $emailMessage);
        self::assertEmailAddressContains($emailMessage, 'to', self::EMAIL);
        self::assertStringContainsString('Confirmation of account registration', $emailMessage->getSubject());

        $user = $this->getCustomerUser(['email' => self::EMAIL]);

        $applicationUrl = self::getConfigManager(null)->get('oro_ui.application_url');
        $confirmMessage = 'Please follow this link to confirm your email address: <a href="'
            . $applicationUrl
            . htmlspecialchars(
                $this->getUrl(
                    'oro_customer_frontend_customer_user_confirmation',
                    [
                        'token' => $user->getConfirmationToken(),
                    ]
                )
            )
            . '">Confirm</a>';
        self::assertStringContainsString($confirmMessage, $emailMessage->getHtmlBody());

        $user = $this->getCustomerUser(['email' => self::EMAIL]);
        self::assertNotEmpty($user);
        self::assertTrue($user->isEnabled());
        self::assertFalse($user->isConfirmed());

        $crawler = $this->client->followRedirect();
        self::assertEquals(
            'Sign In',
            trim($crawler->filter('.login-form h1')->html())
        );
        self::assertStringContainsString('Please check your email to complete registration', $crawler->html());

        $this->client->followRedirects(true);

        // Follow confirmation link
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_confirmation',
                [
                    'token' => $user->getConfirmationToken(),
                ]
            )
        );

        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Sign In', $crawler->html());

        $user = $this->getCustomerUser(['email' => self::EMAIL]);
        self::assertNotEmpty($user);
        self::assertTrue($user->isEnabled());
        self::assertTrue($user->isConfirmed());
    }

    /**
     * @depends testRegisterWithConfirmation
     */
    public function testRegisterExistingEmail(): void
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_register'));
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);

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
                    'second' => self::PASSWORD,
                ],
            ],
        ];

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form, $submittedData);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString(
            'This email is already used.',
            $crawler->filter('.validation-failed')->html()
        );
    }

    public function testResetPasswordWithLowPasswordComplexity(): void
    {
        $user = $this->getCustomerUser(['email' => LoadCustomerUserData::RESET_EMAIL]);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_password_reset',
                [
                    'token' => $user->getConfirmationToken(),
                ]
            )
        );
        $form = $crawler->selectButton('Create')->form();

        $submittedData = [
            'oro_customer_customer_user_password_reset' => [
                '_token' => $form->get('oro_customer_customer_user_password_reset[_token]')->getValue(),
                'plainPassword' => [
                    'first' => '0',
                    'second' => '0',
                ],
            ],
        ];

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form, $submittedData);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('The password must be at least 2 characters long', $crawler->html());
    }

    /**
     * @depends testRegisterWithConfirmation
     */
    public function testResetPassword(): void
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_customer_user_security_login'));
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertEquals(
            'Sign In',
            trim($crawler->filter('.login-form h1')->html())
        );

        $forgotPasswordLink = $crawler->filter('a:contains("Forgot Your Password?")')->link();
        $crawler = $this->client->click($forgotPasswordLink);
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertEquals('Forgot Your Password?', $crawler->filter('h1')->html());

        $this->assertKnownEmail($crawler);

        // Follow reset password link
        $user = $this->getCustomerUser(['email' => self::EMAIL]);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_password_reset',
                [
                    'token' => $user->getConfirmationToken(),
                ]
            )
        );

        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertEquals('Create New Password', $crawler->filter('h1')->html());

        $form = $crawler->selectButton('Create')->form();

        $submittedData = [
            'oro_customer_customer_user_password_reset' => [
                '_token' => $form->get('oro_customer_customer_user_password_reset[_token]')->getValue(),
                'plainPassword' => [
                    'first' => '654321',
                    'second' => '654321',
                ],
            ],
        ];

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form, $submittedData);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertEquals(
            'Sign In',
            trim($crawler->filter('.login-form h1')->html())
        );
        self::assertStringContainsString('Password was created successfully.', $crawler->html());
    }

    private function getCustomerUser(array $criteria): ?CustomerUser
    {
        return self::getContainer()->get('doctrine')->getRepository(CustomerUser::class)
            ->findOneBy($criteria);
    }

    private function submitRegisterForm(Crawler $crawler, string $email): Crawler
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
                    'second' => self::PASSWORD,
                ],
            ],
        ];

        $this->client->followRedirects(false);

        return $this->client->submit($form, $submittedData);
    }

    private function assertKnownEmail(Crawler $crawler): void
    {
        $form = $crawler->selectButton('Request')->form();
        $submittedData = [
            'oro_customer_customer_user_password_request' => [
                '_token' => $form->get('oro_customer_customer_user_password_request[_token]')->getValue(),
                'email' => self::EMAIL,
            ],
        ];

        $this->client->followRedirects(false);
        $this->client->submit($form, $submittedData);

        $emailMessages = self::getMailerMessages();
        self::assertCount(1, $emailMessages);

        /** @var SymfonyEmail $emailMessage */
        $emailMessage = array_shift($emailMessages);

        self::assertEmailAddressContains($emailMessage, 'to', self::EMAIL);
        self::assertStringContainsString('Reset Account User Password', $emailMessage->getSubject());
        self::assertStringContainsString('To reset your password - please visit', $emailMessage->getHtmlBody());
        self::assertStringContainsString(self::EMAIL, $emailMessage->getHtmlBody());

        $user = $this->getCustomerUser(['email' => self::EMAIL]);
        $applicationUrl = self::getConfigManager(null)->get('oro_ui.application_url');

        $resetUrl = $applicationUrl
            . htmlspecialchars(
                $this->getUrl(
                    'oro_customer_frontend_customer_user_password_reset',
                    [
                        'token' => $user->getConfirmationToken(),
                    ]
                )
            );

        self::assertStringContainsString($resetUrl, $emailMessage->getHtmlBody());

        $crawler = $this->client->followRedirect();

        self::assertEquals('Check Email', $crawler->filter('h1')->html());
    }

    public function testConfirmEmailSameUsernameForUserAndVisitor(): void
    {
        $user = $this->getCustomerUser(['email' => LoadUserAndGuestWithSameUsername::SAME_EMAIL, 'isGuest' => false]);
        self::assertNotEmpty($user);
        self::assertTrue($user->isEnabled());
        self::assertFalse($user->isConfirmed());
        self::assertNotNull($user->getConfirmationToken());

        $this->client->followRedirects(true);

        // Follow confirmation link
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_customer_frontend_customer_user_confirmation',
                [
                    'token' => $user->getConfirmationToken(),
                ]
            )
        );

        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);

        $user = $this->getCustomerUser(['email' => LoadUserAndGuestWithSameUsername::SAME_EMAIL, 'isGuest' => false]);
        self::assertNotEmpty($user);
        self::assertTrue($user->isEnabled());
        self::assertTrue($user->isConfirmed());
    }
}
