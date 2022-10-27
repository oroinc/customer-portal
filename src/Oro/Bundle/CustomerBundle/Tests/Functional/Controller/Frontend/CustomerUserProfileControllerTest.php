<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserProfileControllerTest extends WebTestCase
{
    /**
     * @var array
     */
    public static $labels = [
        'Name Prefix',
        'First Name',
        'Middle Name',
        'Last Name',
        'Name Suffix',
        'Birthday',
        'Email Address',
        'Company Name',
        'Roles'
    ];

    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );
        $this->client->useHashNavigation(true);
    }

    public function testViewProfile()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_profile'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $positions = $crawler->filter('.customer-info-grid .grid__row div.control-group label.control-label');

        /**
         * @var \DOMElement $position
         */
        foreach ($positions as $key => $position) {
            $this->assertEquals(self::$labels[$key], $position->textContent);
        }

        self::assertStringContainsString(
            LoadCustomerUserData::AUTH_USER,
            $crawler->filter('.customer-profile')->html()
        );
    }

    public function testEditProfilePageHasSameSiteCancelUrl(): void
    {
        $referer = 'http://example.org';
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_frontend_customer_user_profile_update'),
            [],
            [],
            ['HTTP_REFERER' => $referer]
        );

        self::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        $backToUrl = $crawler->selectLink('Cancel')->attr('href');
        self::assertNotEquals($referer, $backToUrl);
    }

    public function testEditProfile()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_profile_update'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form->offsetSet('oro_customer_frontend_customer_user_profile[firstName]', 'CustomerUserUpdated');

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        self::assertStringContainsString('CustomerUserUpdated', $crawler->filter('.customer-profile')->html());
    }

    public function testEditProfilePasswordMismatch()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_profile_update'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form->offsetSet(
            'oro_customer_frontend_customer_user_profile[changePassword]',
            [
                'currentPassword' => LoadCustomerUserData::AUTH_PW,
                'plainPassword' => [
                    'first' => '123456',
                    'second' => '654321',
                ]
            ]
        );

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        self::assertStringContainsString(
            'The password fields must match.',
            $crawler->filter('.password_first span')->html()
        );
    }

    public function testEditProfileWithoutCurrentPassword()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_frontend_customer_user_profile_update'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form->offsetSet(
            'oro_customer_frontend_customer_user_profile[changePassword]',
            [
                'currentPassword' => '123456',
                'plainPassword' => [
                    'first' => '123456',
                    'second' => '123456',
                ]
            ]
        );
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        self::assertStringContainsString(
            'This value should be the user\'s current password.',
            $crawler->filter('.current_password span')->html()
        );
    }
}
