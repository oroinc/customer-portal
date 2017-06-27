<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional;

use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserACLData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class GuestAccessTest extends WebTestCase
{
    /**
     * @internal
     */
    const CONFIG_GUEST_ACCESS_ENABLED = 'oro_frontend.guest_access_enabled';

    /**
     * @internal
     */
    const WEB_BACKEND_ = '/admin/user/login';

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadCustomerUserACLData::class]);

        $this->setGuestAccess(false);
    }

    protected function tearDown()
    {
        $this->setGuestAccess(true);
    }

    public function testBackOfficeIsAccessible()
    {
        $this->client->request('GET', $this->getBackOfficeLoginUrl());
        $response = $this->client->getResponse();

        static::assertResponseStatusCodeEquals($response, 200);
    }

    /**
     * @dataProvider allowedUrlsDataProvider
     *
     * @param string $url
     */
    public function testAllowedUrls($url)
    {
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        static::assertResponseStatusCodeEquals($response, 200);
    }

    /**
     * @return array
     */
    public function allowedUrlsDataProvider()
    {
        return [
            ['/customer/user/login'],
            ['/customer/user/reset-request'],
            ['/customer/user/registration'],
            ['/customer/user/registration?ref=ref-id'],
        ];
    }

    /**
     * @dataProvider disallowedUrlsDataProvider
     *
     * @param string $url
     */
    public function testDisallowedUrls($url)
    {
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        static::assertResponseStatusCodeEquals($response, 302);
        static::assertTrue($response->isRedirect('/customer/user/login'));
    }

    /**
     * @return array
     */
    public function disallowedUrlsDataProvider()
    {
        return [
            ['/'],
            ['/about'],
        ];
    }

    /**
     * @dataProvider allowedUrlsWhenAuthenticatedDataProvider
     *
     * @param string $url
     */
    public function testAllowedUrlsWhenAuthenticated($url)
    {
        $this->loginUser(LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL);
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        static::assertResponseStatusCodeEquals($response, 200);
    }

    /**
     * @return array
     */
    public function allowedUrlsWhenAuthenticatedDataProvider()
    {
        return [
            ['/'],
            ['/customer/profile/'],
        ];
    }

    /**
     * @return string
     */
    private function getBackOfficeLoginUrl()
    {
        return $this->getUrl('oro_user_security_login');
    }

    /**
     * @param bool $guestAccessEnabled
     */
    private function setGuestAccess($guestAccessEnabled)
    {
        $configManager = static::getContainer()->get('oro_config.manager');
        $configManager->set(self::CONFIG_GUEST_ACCESS_ENABLED, (bool) $guestAccessEnabled);
        $configManager->flush();
    }
}
