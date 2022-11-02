<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Controller;

use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class FrontendControllerTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private const FRONTEND_THEME_CONFIG_KEY = 'oro_frontend.frontend_theme';

    protected function setUp(): void
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->setDefaultTheme();

        $this->loadFixtures([LoadProductData::class]);
    }

    protected function tearDown(): void
    {
        $this->setDefaultTheme();
    }

    public function testIndexPage()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_frontend_root'));
        self::assertStringNotContainsString($this->getBackendPrefix(), $crawler->html());
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testThemeSwitch()
    {
        // Switch to layout theme
        $layoutTheme = 'default';
        $this->setTheme($layoutTheme);

        $this->client->request('GET', $this->getUrl('oro_frontend_root'));
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);

        // Check that backend theme was not affected
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_user_security_login'),
            [],
            [],
            $this->generateNoHashNavigationHeader()
        );
        $this->assertEquals('Login', $crawler->filter('h2.title')->html());

        // Check that after selecting of layout there is an ability to switch to oro theme
        $this->setDefaultTheme();

        $this->client->request('GET', $this->getUrl('oro_frontend_root'));
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);
    }

    private function getBackendPrefix(): string
    {
        return self::getContainer()->getParameter('web_backend_prefix');
    }

    private function setTheme(string $theme): void
    {
        $configManager = self::getConfigManager();
        $configManager->set(self::FRONTEND_THEME_CONFIG_KEY, $theme);
        $configManager->flush();
    }

    private function setDefaultTheme(): void
    {
        $configManager = self::getConfigManager();
        $configManager->reset(self::FRONTEND_THEME_CONFIG_KEY);
        $configManager->flush();
    }
}
