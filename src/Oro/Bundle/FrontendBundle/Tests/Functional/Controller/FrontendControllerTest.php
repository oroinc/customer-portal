<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Controller;

use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class FrontendControllerTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private ?string $initialTheme;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadProductData::class]);

        $this->initialTheme = self::getConfigManager()->get('oro_frontend.frontend_theme');
    }

    #[\Override]
    protected function tearDown(): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_frontend.frontend_theme', $this->initialTheme);
        $configManager->flush();
    }

    public function testIndexPage(): void
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_frontend_root'));
        self::assertStringNotContainsString(
            self::getContainer()->getParameter('web_backend_prefix'),
            $crawler->html()
        );
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testThemeSwitch(): void
    {
        // Switch to layout theme
        $configManager = self::getConfigManager();
        $configManager->set('oro_frontend.frontend_theme', 'default');
        $configManager->flush();

        $this->client->request('GET', $this->getUrl('oro_frontend_root'));
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);

        // Check that backend theme was not affected
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_user_security_login'),
            [],
            [],
            self::generateNoHashNavigationHeader()
        );
        self::assertEquals('Login', $crawler->filter('h2.title')->html());

        // Check that after selecting of layout there is an ability to switch to oro theme
        $configManager->set('oro_frontend.frontend_theme', $this->initialTheme);
        $configManager->flush();

        $this->client->request('GET', $this->getUrl('oro_frontend_root'));
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);
    }
}
