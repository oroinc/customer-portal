<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Layout\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\ItemInterface;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerVisitors;
use Oro\Bundle\FrontendBundle\Layout\DataProvider\ThemeHeaderConfigProvider;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\ThemeBundle\DependencyInjection\Configuration;
use Oro\Bundle\ThemeBundle\Entity\ThemeConfiguration as ThemeConfigurationEntity;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadContentNodesData;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadWebCatalogData;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadWebCatalogScopes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ThemeHeaderConfigProviderTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private ThemeHeaderConfigProvider $provider;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([
            LoadCustomerUser::class,
            LoadCustomerVisitors::class,
            LoadContentNodesData::class,
            LoadWebCatalogScopes::class,
        ]);
        $this->provider = self::getContainer()->get('oro_frontend.layout.data_provider.theme_header_config');
    }

    protected function tearDown(): void
    {
        self::getContainer()->get('security.token_storage')->setToken(null);
        self::getContainer()->get(FrontendHelper::class)->resetRequestEmulation();
        parent::tearDown();
    }

    private function getThemeConfiguration(): ThemeConfigurationEntity
    {
        return $this->getThemeConfigurationEntityManager()->find(
            ThemeConfigurationEntity::class,
            self::getConfigManager()->get(Configuration::getConfigKeyByName(Configuration::THEME_CONFIGURATION))
        );
    }

    private function getThemeConfigurationEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManagerForClass(ThemeConfigurationEntity::class);
    }

    private function setupRequestStack(): void
    {
        self::getContainer()->get(FrontendHelper::class)->emulateFrontendRequest();
        $request = Request::create('/');

        /** @see \Oro\Bundle\WebCatalogBundle\Provider\RequestWebContentScopeProvider::REQUEST_SCOPES_ATTRIBUTE */
        $request->attributes->set(
            '_web_content_scopes',
            [$this->getReference(LoadWebCatalogScopes::SCOPE1)]
        );
        self::getContainer()->get(RequestStack::class)->push($request);
    }

    private function setQuickAccessButtonForThemeConfiguration(QuickAccessButtonConfig $quickAccessButtonConfig): void
    {
        $this->getThemeConfiguration()->addConfigurationOption(
            ThemeConfiguration::buildOptionKey('header', 'quick_access_button'),
            $quickAccessButtonConfig
        );
        $this->getThemeConfigurationEntityManager()->flush();
    }

    public function testGetQuickAccessButtonDefaultValue(): void
    {
        self::assertNull($this->provider->getQuickAccessButton());
        self::assertNull($this->provider->getQuickAccessButtonLabel());
    }

    public function testGetQuickAccessButtonForExistingMenu(): void
    {
        $quickAccessButtonConfig = (new QuickAccessButtonConfig())
            ->setLabel(['' => 'label'])
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('frontend_menu');
        $this->setQuickAccessButtonForThemeConfiguration($quickAccessButtonConfig);

        self::assertInstanceOf(ItemInterface::class, $this->provider->getQuickAccessButton());
        self::assertEquals('label', $this->provider->getQuickAccessButtonLabel());
    }

    public function testGetQuickAccessButtonForNonExistingMenu(): void
    {
        $quickAccessButtonConfig = (new QuickAccessButtonConfig())
            ->setLabel(['' => 'label'])
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('frontend_menu_34gtd56');

        $this->setQuickAccessButtonForThemeConfiguration($quickAccessButtonConfig);

        self::assertNull($this->provider->getQuickAccessButton());
        self::assertEquals('label', $this->provider->getQuickAccessButtonLabel());
    }

    public function testGetQuickAccessButtonForWebCatalogNode(): void
    {
        $this->setupRequestStack();

        $webCatalog = $this->getReference(LoadWebCatalogData::CATALOG_1);
        /** @var ContentNode $node */
        $node = $this->getReference(LoadContentNodesData::CATALOG_1_ROOT);

        $quickAccessButtonConfig = (new QuickAccessButtonConfig())
            ->setLabel(['' => 'wcn label'])
            ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
            ->setWebCatalogNode($node->getId());

        $this->setQuickAccessButtonForThemeConfiguration($quickAccessButtonConfig);

        $configManager = self::getConfigManager();
        $configManager->set('oro_web_catalog.web_catalog', $webCatalog->getId());
        $configManager->flush();

        self::assertInstanceOf(ItemInterface::class, $this->provider->getQuickAccessButton());
        self::assertEquals('wcn label', $this->provider->getQuickAccessButtonLabel());
        self::assertEmpty($this->provider->getQuickAccessButton()->getChildren());
    }

    public function testGetQuickAccessButtonForNonAvailableWebCatalogNode(): void
    {
        $this->setupRequestStack();

        $webCatalog = $this->getReference(LoadWebCatalogData::CATALOG_1);

        $quickAccessButtonConfig = (new QuickAccessButtonConfig())
            ->setLabel(['' => 'label'])
            ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
            ->setWebCatalogNode(-1);

        $this->setQuickAccessButtonForThemeConfiguration($quickAccessButtonConfig);

        $configManager = self::getConfigManager();
        $configManager->set('oro_web_catalog.web_catalog', $webCatalog->getId());
        $configManager->flush();

        self::assertNull($this->provider->getQuickAccessButton());
        self::assertEquals('label', $this->provider->getQuickAccessButtonLabel());
    }

    public function testGetQuickAccessButtonForNonDefaultWebCatalogNode(): void
    {
        $this->setupRequestStack();

        $webCatalog = $this->getReference(LoadWebCatalogData::CATALOG_1);
        /** @var ContentNode $node */
        $node = $this->getReference(LoadContentNodesData::CATALOG_2_ROOT);

        $quickAccessButtonConfig = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
            ->setWebCatalogNode($node->getId());

        $this->setQuickAccessButtonForThemeConfiguration($quickAccessButtonConfig);

        $configManager = self::getConfigManager();
        $configManager->set('oro_web_catalog.web_catalog', $webCatalog->getId());
        $configManager->flush();

        self::assertNull($this->provider->getQuickAccessButton());
        self::assertNull($this->provider->getQuickAccessButtonLabel());
    }
}
