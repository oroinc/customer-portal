<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserACLData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class CustomerUserMenuTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    /** @var ConfigManager */
    private $configManager;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCustomerUserACLData::class]);

        $this->configManager = self::getConfigManager();
    }

    /**
     * Check the 404 page if the menu is rendered in the popup and url address is not found in matches the menu list.
     */
    public function testNotFoundPage(): void
    {
        $this->configManager->set('oro_customer.user_menu_show_items', 'subitems_in_popup');
        $this->configManager->flush();

        $this->loginUser(LoadCustomerUserACLData::USER_ACCOUNT_1_ROLE_LOCAL);
        $crawler = $this->client->request(Request::METHOD_GET, '/not_found');
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 404);
        $this->assertPageTitleSame('Not Found');

        $menu = $crawler->filterXPath('//ul[contains(@class, "topbar-customer-menu-list")]/li');
        // Only 3 items as there are no other permissions
        $this->assertCount(3, $menu);
    }
}
