<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Layout\DataProvider;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CMSBundle\Tests\Functional\DataFixtures\LoadTextContentVariantsData;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerVisitors;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\FrontendBundle\Layout\DataProvider\ThemeHeaderConfigProvider;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadContentNodesData;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadWebCatalogData;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadWebCatalogScopes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @dbIsolationPerTest
 */
class ThemeHeaderConfigProviderTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private ThemeHeaderConfigProvider $provider;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([
            LoadTextContentVariantsData::class,
            LoadCustomerUser::class,
            LoadCustomerVisitors::class,
            LoadContentNodesData::class,
            LoadWebCatalogScopes::class,
        ]);
        $this->provider = $this->getClientContainer()->get('oro_frontend.layout.data_provider.theme_header_config');
    }

    protected function tearDown(): void
    {
        $config = self::getConfigManager();
        $config->reset(Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT));
        $config->reset(Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON));
        $this->getContainer()->get('security.token_storage')->setToken(null);
        $this->getClientContainer()->get(FrontendHelper::class)->resetRequestEmulation();
        parent::tearDown();
    }

    public function testGetPromotionalBlockAliasForVisitors(): void
    {
        /** @var CustomerVisitor $visitor */
        $visitor = $this->getReference(LoadCustomerVisitors::CUSTOMER_VISITOR);
        $this->getContainer()
            ->get('security.token_storage')
            ->setToken(new AnonymousCustomerUserToken(
                $visitor,
                [],
                $this->getReference(LoadOrganization::ORGANIZATION)
            ));

        $config = self::getConfigManager();
        $config->set(
            Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT),
            $this->getReference('content_block_1')->getId()
        );
        $config->flush();
        self::assertEquals('content_block_1', $this->provider->getPromotionalBlockAlias());
    }

    public function testGetPromotionalBlockAliasForCustomerUser(): void
    {
        /** @var CustomerUser $user */
        $user = $this->getReference(LoadCustomerUser::CUSTOMER_USER);
        $this->getContainer()
            ->get('security.token_storage')
            ->setToken(new UsernamePasswordOrganizationToken(
                $user,
                'k',
                $user->getOrganization(),
                $user->getUserRoles()
            ));

        $config = self::getConfigManager();
        $config->set(
            Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT),
            $this->getReference('content_block_1')->getId(),
        );
        $config->flush();
        self::assertEquals('content_block_1', $this->provider->getPromotionalBlockAlias());
    }

    public function testGetPromotionalBlockAliasForAnonymous(): void
    {
        $this->getContainer()
            ->get('security.token_storage')
            ->setToken(null);

        $config = self::getConfigManager();
        $config->set(
            Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT),
            $this->getReference('content_block_1')->getId(),
        );
        $config->flush();
        self::assertEquals('content_block_1', $this->provider->getPromotionalBlockAlias());
    }

    public function testGetQuickAccessButtonDefaultValue(): void
    {
        self::assertTrue(null === $this->provider->getQuickAccessButton());
        self::assertTrue(null === $this->provider->getQuickAccessButtonLabel());
    }

    public function testGetQuickAccessButtonForExistingMenu(): void
    {
        $config = self::getConfigManager();
        $config->set(
            Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON),
            (new QuickAccessButtonConfig())
                ->setType(QuickAccessButtonConfig::TYPE_MENU)
                ->setMenu('frontend_menu')
        );
        $config->flush();

        self::assertInstanceOf(ItemInterface::class, $this->provider->getQuickAccessButton());

        /** TODO: Add test to check expected label for root menu item at BB-23579 */
        self::assertNotEmpty($this->provider->getQuickAccessButtonLabel());
    }

    public function testGetQuickAccessButtonForNonExistingMenu(): void
    {
        $config = self::getConfigManager();
        $config->set(
            Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON),
            (new QuickAccessButtonConfig())
                ->setType(QuickAccessButtonConfig::TYPE_MENU)
                ->setMenu('frontend_menu_34gtd56')
        );
        $config->flush();

        self::assertTrue(null === $this->provider->getQuickAccessButton());
        self::assertTrue(null === $this->provider->getQuickAccessButtonLabel());
    }

    public function testGetQuickAccessButtonForWebCatalogNode(): void
    {
        $this->setupRequestStack();

        $config = self::getConfigManager();

        $webCatalog = $this->getReference(LoadWebCatalogData::CATALOG_1);
        /** @var ContentNode $node */
        $node = $this->getReference(LoadContentNodesData::CATALOG_1_ROOT);

        $config->set(
            Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON),
            (new QuickAccessButtonConfig())
                ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
                ->setWebCatalogNode($node->getId())
        );
        $config->set('oro_web_catalog.web_catalog', $webCatalog->getId());

        $config->flush();

        self::assertInstanceOf(ItemInterface::class, $this->provider->getQuickAccessButton());
        self::assertEquals('web_catalog.node.1.root', $this->provider->getQuickAccessButtonLabel());
        self::assertEmpty($this->provider->getQuickAccessButton()->getChildren());
    }

    public function testGetQuickAccessButtonForNonAvailableWebCatalogNode(): void
    {
        $this->setupRequestStack();

        $config = self::getConfigManager();

        $webCatalog = $this->getReference(LoadWebCatalogData::CATALOG_1);

        $config->set(
            Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON),
            (new QuickAccessButtonConfig())
                ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
                ->setWebCatalogNode(-1)
        );
        $config->set('oro_web_catalog.web_catalog', $webCatalog->getId());

        $config->flush();

        self::assertTrue(null === $this->provider->getQuickAccessButton());
        self::assertTrue(null === $this->provider->getQuickAccessButtonLabel());
    }

    public function testGetQuickAccessButtonForNonDefaultWebCatalogNode(): void
    {
        $this->setupRequestStack();

        $config = self::getConfigManager();

        $webCatalog = $this->getReference(LoadWebCatalogData::CATALOG_1);
        /** @var ContentNode $node */
        $node = $this->getReference(LoadContentNodesData::CATALOG_2_ROOT);

        $config->set(
            Configuration::getConfigKeyByName(Configuration::QUICK_ACCESS_BUTTON),
            (new QuickAccessButtonConfig())
                ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
                ->setWebCatalogNode($node->getId())
        );
        $config->set('oro_web_catalog.web_catalog', $webCatalog->getId());

        $config->flush();

        self::assertTrue(null === $this->provider->getQuickAccessButton());
        self::assertTrue(null === $this->provider->getQuickAccessButtonLabel());
    }

    private function setupRequestStack(): void
    {
        $this->getClientContainer()->get(FrontendHelper::class)->emulateFrontendRequest();
        $request = Request::create('/');

        $request->attributes->set(
            /** @see \Oro\Bundle\WebCatalogBundle\Provider\RequestWebContentScopeProvider::REQUEST_SCOPES_ATTRIBUTE */
            '_web_content_scopes',
            [$this->getReference(LoadWebCatalogScopes::SCOPE1)]
        );
        $this->getClientContainer()->get(RequestStack::class)->push($request);
    }
}
