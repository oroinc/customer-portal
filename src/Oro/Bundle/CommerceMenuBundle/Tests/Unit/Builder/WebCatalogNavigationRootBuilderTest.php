<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\Util\MenuManipulator;
use Oro\Bundle\CommerceMenuBundle\Builder\WebCatalogNavigationRootBuilder;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\PlatformBundle\Tests\Unit\Stub\ProxyStub;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentVariant;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Menu\MenuContentNodesProviderInterface;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Component\Website\WebsiteInterface;

class WebCatalogNavigationRootBuilderTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;
    use MenuItemTestTrait;

    private const MENU_TEMPLATE = 'template1';

    private WebCatalogProvider|\PHPUnit\Framework\MockObject\MockObject $webCatalogProvider;

    private MenuContentNodesProviderInterface|\PHPUnit\Framework\MockObject\MockObject $menuContentNodesProvider;

    private WebCatalogNavigationRootBuilder $builder;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->webCatalogProvider = $this->createMock(WebCatalogProvider::class);
        $this->menuContentNodesProvider = $this->createMock(MenuContentNodesProviderInterface::class);
        $menuTemplatesProvider = $this->createMock(MenuTemplatesProvider::class);

        $this->builder = new WebCatalogNavigationRootBuilder(
            $managerRegistry,
            $this->webCatalogProvider,
            $this->menuContentNodesProvider,
            $menuTemplatesProvider
        );

        $this->setUpLoggerMock($this->builder);

        $entityManager = $this->createMock(EntityManager::class);
        $managerRegistry
            ->expects(self::any())
            ->method('getManagerForClass')
            ->with(ContentNode::class)
            ->willReturn($entityManager);

        $entityManager
            ->expects(self::any())
            ->method('getReference')
            ->willReturnCallback(static fn ($class, $id) => new ProxyStub($class, $id));

        $menuTemplatesProvider
            ->expects(self::any())
            ->method('getMenuTemplates')
            ->willReturn([
                self::MENU_TEMPLATE => ['label' => 'Template 1'],
                'template2' => ['label' => 'Template 2'],
            ]);
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $this->webCatalogProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->menuContentNodesProvider
            ->expects(self::never())
            ->method(self::anything());

        $menu = $this->createItem('sample_menu');
        $menu->setDisplay(false);
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    public function testBuildWhenInvalidWebsite(): void
    {
        $this->webCatalogProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->menuContentNodesProvider
            ->expects(self::never())
            ->method(self::anything());

        $website = new \stdClass();
        $this->loggerMock
            ->expects(self::once())
            ->method('error')
            ->with(
                'Option "website" with value {actual_type} is expected to be {expected_type}',
                ['actual_type' => get_debug_type($website), 'expected_type' => WebsiteInterface::class]
            );

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu, ['website' => $website]);

        self::assertEmpty($menu->getChildren());
    }

    public function testBuildWhenNoRootContentNode(): void
    {
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getNavigationRootWithCatalogRootFallback')
            ->willReturn(null);

        $this->menuContentNodesProvider
            ->expects(self::never())
            ->method(self::anything());

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    public function testBuildWhenNoResolvedRootContentNode(): void
    {
        $rootContentNode = (new ContentNode())
            ->setWebCatalog((new WebCatalog())->setName('Sample Catalog'));
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getNavigationRootWithCatalogRootFallback')
            ->willReturn($rootContentNode);

        $this->menuContentNodesProvider
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($rootContentNode)
            ->willReturn(null);

        $this->loggerMock
            ->expects(self::once())
            ->method('debug')
            ->with(
                'Content node #{node_id} from web catalog #{web_catalog} is not resolved, skipping it.',
                ['node_id' => $rootContentNode->getId(), $rootContentNode->getWebCatalog()->getName()]
            );

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    /**
     * @dataProvider buildDataProvider
     */
    public function testBuild(ResolvedContentNode $resolvedNode, array $expected): void
    {
        $rootContentNode = (new ContentNode())
            ->setWebCatalog((new WebCatalog())->setName('Sample Catalog'));
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getNavigationRootWithCatalogRootFallback')
            ->willReturn($rootContentNode);

        $this->menuContentNodesProvider
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($rootContentNode)
            ->willReturn($resolvedNode);

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu);

        $menuManipulator = new MenuManipulator();
        self::assertEquals($expected, $menuManipulator->toArray($menu));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildDataProvider(): array
    {
        return [
            'no children' => [
                'resolvedNode' => $this->createResolvedNode(1),
                'expected' => [
                    'name' => 'sample_menu',
                    'label' => 'sample_menu',
                    'uri' => null,
                    'attributes' => [],
                    'labelAttributes' => [],
                    'linkAttributes' => [],
                    'childrenAttributes' => [],
                    'extras' => [],
                    'display' => true,
                    'displayChildren' => true,
                    'current' => null,
                    'children' => [],
                ],
            ],
            'has children' => [
                'resolvedNode' => $this->createResolvedNode(1)
                    ->addChildNode($this->createResolvedNode(11))
                    ->addChildNode($this->createResolvedNode(12)),
                'expected' => [
                    'name' => 'sample_menu',
                    'label' => 'sample_menu',
                    'uri' => null,
                    'attributes' => [],
                    'labelAttributes' => [],
                    'linkAttributes' => [],
                    'childrenAttributes' => [],
                    'extras' => [],
                    'display' => true,
                    'displayChildren' => true,
                    'current' => null,
                    'children' => [
                        'content_node_11' => [
                            'name' => 'content_node_11',
                            'label' => 'content_node_11',
                            'uri' => null,
                            'attributes' => [],
                            'labelAttributes' => [],
                            'linkAttributes' => [],
                            'childrenAttributes' => [],
                            'extras' => [
                                'isAllowed' => true,
                                'content_node' => new ProxyStub(ContentNode::class, 11),
                                'position' => -102,
                                'menu_template' => 'template1',
                                'max_traverse_level' => 0,
                            ],
                            'display' => true,
                            'displayChildren' => true,
                            'current' => null,
                            'children' => [],
                        ],
                        'content_node_12' => [
                            'name' => 'content_node_12',
                            'label' => 'content_node_12',
                            'uri' => null,
                            'attributes' => [],
                            'labelAttributes' => [],
                            'linkAttributes' => [],
                            'childrenAttributes' => [],
                            'extras' => [
                                'isAllowed' => true,
                                'content_node' => new ProxyStub(ContentNode::class, 12),
                                'position' => -101,
                                'menu_template' => 'template1',
                                'max_traverse_level' => 0,
                            ],
                            'display' => true,
                            'displayChildren' => true,
                            'current' => null,
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider buildDataProvider
     */
    public function testBuildWithWebsite(ResolvedContentNode $resolvedNode, array $expected): void
    {
        $website = $this->createMock(WebsiteInterface::class);
        $rootContentNode = (new ContentNode())
            ->setWebCatalog((new WebCatalog())->setName('Sample Catalog'));
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getNavigationRootWithCatalogRootFallback')
            ->with($website)
            ->willReturn($rootContentNode);

        $this->menuContentNodesProvider
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($rootContentNode)
            ->willReturn($resolvedNode);

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu, ['website' => $website]);

        $menuManipulator = new MenuManipulator();
        self::assertEquals($expected, $menuManipulator->toArray($menu));
    }

    private function createResolvedNode(int $id): ResolvedContentNode
    {
        return new ResolvedContentNode(
            $id,
            'root__' . $id,
            0,
            new ArrayCollection(),
            new ResolvedContentVariant()
        );
    }
}
