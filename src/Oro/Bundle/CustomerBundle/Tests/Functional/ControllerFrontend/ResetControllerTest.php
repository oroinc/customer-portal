<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ControllerFrontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ResetControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->client->followRedirects();
    }

    public function testResetWithEmptyToken()
    {
        $this->client->request('GET', $this->getUrl(
            'oro_customer_frontend_customer_user_password_reset'
        ));

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 404);
    }

    public function testResetWithUnknownToken()
    {
        $this->client->request('GET', $this->getUrl(
            'oro_customer_frontend_customer_user_password_reset',
            ['token' => 'unknown']
        ));

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 404);
    }

    public function testResetWithTokenButNoPasswordRequest()
    {
        // Regression guard for BB-27642: a confirmation token with no TTL anchor (as minted by
        // email-verification, or by an old install predating this fix) must never be redeemable
        // for password reset, no matter how "fresh" the token itself looks.
        $this->loadFixtures([LoadCustomerUserData::class]);

        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUserData::RESET_EMAIL);
        $user->setPasswordRequestedAt(null);
        $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class)->flush();

        $this->client->request('GET', $this->getUrl(
            'oro_customer_frontend_customer_user_password_reset',
            ['token' => $user->getConfirmationToken()]
        ));

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('The reset password link has expired.', $result->getContent());
    }
}
