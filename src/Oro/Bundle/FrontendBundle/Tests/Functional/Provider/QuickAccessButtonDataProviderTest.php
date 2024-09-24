<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Provider;

use Knp\Menu\ItemInterface;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerVisitors;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\FrontendBundle\Provider\QuickAccessButtonDataProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadContentNodesData;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadWebCatalogData;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadWebCatalogScopes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class QuickAccessButtonDataProviderTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private QuickAccessButtonDataProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([
            LoadCustomerUser::class,
            LoadCustomerVisitors::class,
            LoadContentNodesData::class,
            LoadWebCatalogScopes::class,
        ]);
        $this->provider = $this->getClientContainer()->get('oro_frontend.provider.quick_access_button_data');
    }

    #[\Override]
    protected function tearDown(): void
    {
        self::getContainer()->get('security.token_storage')->setToken(null);
        $this->getClientContainer()->get(FrontendHelper::class)->resetRequestEmulation();
        parent::tearDown();
    }

    public function testGetLabel(): void
    {
        self::assertNull($this->provider->getLabel(new QuickAccessButtonConfig()));
        self::assertEquals(
            'label',
            $this->provider->getLabel((new QuickAccessButtonConfig())->setLabel(['' => 'label']))
        );
    }

    public function testGetMenuIfDisabled(): void
    {
        $config = (new QuickAccessButtonConfig())
            ->setType(null);

        self::assertTrue(null === $this->provider->getMenu($config));
    }

    public function testGetMenuIfNonExistingFrontendMenuSelected(): void
    {
        $config = (new QuickAccessButtonConfig())
            ->setType('menu')
            ->setMenu('some_non_existing_menu');

        self::assertTrue(null === $this->provider->getMenu($config));
    }

    public function testGetMenuExistingFrontendMenuSelected(): void
    {
        $config = (new QuickAccessButtonConfig())
            ->setType('menu')
            ->setMenu('frontend_menu');

        self::assertInstanceOf(ItemInterface::class, $this->provider->getMenu($config));
    }

    public function testGetMenuNonAccessibleWebCatalogNodeSelected(): void
    {
        $this->setupRequestStackAndWebCatalog();

        $config = (new QuickAccessButtonConfig())
            ->setType('web_catalog_node')
            ->setWebCatalogNode(-1);

        self::assertTrue(null === $this->provider->getMenu($config));
    }

    public function testGetMenuAccessibleWebCatalogNodeSelected(): void
    {
        $this->setupRequestStackAndWebCatalog();

        /** @var ContentNode $node */
        $node = $this->getReference(LoadContentNodesData::CATALOG_1_ROOT);

        $config = (new QuickAccessButtonConfig())
            ->setLabel(['' => 'wcn label'])
            ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
            ->setWebCatalogNode($node->getId());

        $menu = $this->provider->getMenu($config);
        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertCount(0, $menu->getChildren());
    }

    private function setupRequestStackAndWebCatalog(): void
    {
        $webCatalog = $this->getReference(LoadWebCatalogData::CATALOG_1);
        self::getConfigManager()->set('oro_web_catalog.web_catalog', $webCatalog->getId());

        $this->getClientContainer()->get(FrontendHelper::class)->emulateFrontendRequest();
        $request = Request::create('/');

        /** @see \Oro\Bundle\WebCatalogBundle\Provider\RequestWebContentScopeProvider::REQUEST_SCOPES_ATTRIBUTE */
        $request->attributes->set(
            '_web_content_scopes',
            [$this->getReference(LoadWebCatalogScopes::SCOPE1)]
        );
        $this->getClientContainer()->get(RequestStack::class)->push($request);
    }
}
