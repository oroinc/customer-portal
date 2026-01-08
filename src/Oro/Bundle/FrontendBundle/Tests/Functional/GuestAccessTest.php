<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional;

use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserACLData;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class GuestAccessTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserACLData::class]);
        $this->setGuestAccess(false);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->setGuestAccess(true);
    }

    private function setGuestAccess(bool $guestAccessEnabled): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_frontend.guest_access_enabled', $guestAccessEnabled);
        $configManager->flush();
    }

    private function getBackOfficeLoginUrl(): string
    {
        return $this->getUrl('oro_user_security_login');
    }

    public function testBackOfficeIsAccessible(): void
    {
        $this->client->request('GET', $this->getBackOfficeLoginUrl());
        $response = $this->client->getResponse();

        self::assertResponseStatusCodeEquals($response, 200);
    }

    /**
     * @dataProvider allowedUrlsDataProvider
     */
    public function testAllowedUrls(string $url): void
    {
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        self::assertResponseStatusCodeEquals($response, 200);
    }

    public function allowedUrlsDataProvider(): array
    {
        return [
            ['/customer/user/login'],
            ['/customer/user/reset-request'],
            ['/customer/user/registration'],
            ['/customer/user/registration?ref=ref-id']
        ];
    }

    /**
     * @dataProvider disallowedUrlsDataProvider
     */
    public function testDisallowedUrls(string $url): void
    {
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        self::assertResponseStatusCodeEquals($response, 302);
        self::assertTrue($response->isRedirect('/customer/user/login'));
    }

    public function disallowedUrlsDataProvider(): array
    {
        return [
            ['/'],
            ['/customer/profile/']
        ];
    }

    /**
     * @dataProvider allowedUrlsWhenAuthenticatedDataProvider
     */
    public function testAllowedUrlsWhenAuthenticated(string $url): void
    {
        $this->markTestSkipped('BAP-20556');
        $this->loginUser(LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL);
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        self::assertResponseStatusCodeEquals($response, 200);
    }

    public function allowedUrlsWhenAuthenticatedDataProvider(): array
    {
        return [
            ['/'],
            ['/customer/profile/']
        ];
    }

    public function testConfiguredSystemPageIsAccessibleWhenGuestAccessDisabled(): void
    {
        $configManager = self::getConfigManager();
        $configKey = Configuration::getConfigKeyByName(Configuration::GUEST_ACCESS_ALLOWED_SYSTEM_PAGES);

        $configManager->set($configKey, ['oro_customer_frontend_customer_user_register']);
        $configManager->flush();

        // Verify the page is accessible even though guest access is disabled
        $this->client->request('GET', '/customer/user/registration');
        $response = $this->client->getResponse();

        self::assertResponseStatusCodeEquals($response, 200);

        // Clean up
        $configManager->set($configKey, []);
        $configManager->flush();
    }

    public function testNonConfiguredSystemPageIsNotAccessibleWhenGuestAccessDisabled(): void
    {
        $configManager = self::getConfigManager();
        $configKey = Configuration::getConfigKeyByName(Configuration::GUEST_ACCESS_ALLOWED_SYSTEM_PAGES);

        $configManager->set($configKey, ['oro_customer_frontend_customer_user_register']);
        $configManager->flush();

        // Verify other pages are still redirected
        $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        self::assertResponseStatusCodeEquals($response, 302);
        self::assertTrue($response->isRedirect('/customer/user/login'));

        // Clean up
        $configManager->set($configKey, []);
        $configManager->flush();
    }
}
