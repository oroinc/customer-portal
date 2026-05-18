<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class FrontendCustomerUserProfileTypeTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::GROUP2_EMAIL, LoadCustomerUserData::GROUP2_PASSWORD)
        );
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    public function testUserChangeEmailToAnotherUserEmail()
    {
        $this->disableEmailApproveFeature();
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_profile_update_email')
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form['frontend_customer_user_profile_email[email]'] = LoadCustomerUserData::EMAIL;
        $form['frontend_customer_user_profile_email[currentPassword]'] = LoadCustomerUserData::GROUP2_PASSWORD;

        $this->client->followRedirects(true);
        $this->disableEmailApproveFeature();
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('This email is already used', $crawler->html());
        self::assertStringNotContainsString('Email updated', $crawler->html());

        /** @var CustomerUser $expectedUser */
        $expectedUser = $this->getReference(LoadCustomerUserData::GROUP2_EMAIL);
        $actualUsername = $this->getContainer()->get('security.token_storage')->getToken()->getUserIdentifier();

        $this->assertEquals($expectedUser->getUserIdentifier(), $actualUsername);
    }

    private function disableEmailApproveFeature(): void
    {
        $configManager = self::getContainer()->get('oro_config.manager');
        $configManager->set('oro_customer.email_change_verification_enabled', false);
        $configManager->flush();
    }
}
