<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\ContentNodeTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\PlatformBundle\Tests\Unit\Stub\ProxyStub;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentVariant;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Menu\MenuContentNodesProviderInterface;
use Oro\Bundle\WebCatalogBundle\Tests\Unit\Stub\ContentNodeStub;

class ContentNodeTreeBuilderTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private MenuContentNodesProviderInterface $menuContentNodesProvider;

    private ContentNodeTreeBuilder $builder;

    private EntityManager|\PHPUnit\Framework\MockObject\MockObject $entityManager;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->menuContentNodesProvider = $this->createMock(MenuContentNodesProviderInterface::class);
        $localizationHelper = $this->createMock(LocalizationHelper::class);

        $this->builder = new ContentNodeTreeBuilder(
            $managerRegistry,
            $this->menuContentNodesProvider,
            $localizationHelper
        );

        $this->entityManager = $this->createMock(EntityManager::class);
        $managerRegistry
            ->expects(self::any())
            ->method('getManagerForClass')
            ->with(ContentNode::class)
            ->willReturn($this->entityManager);

        $localizationHelper
            ->expects(self::any())
            ->method('getLocalizedValue')
            ->willReturnCallback(static fn ($collection) => (string)($collection[0] ?? null));
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $contentNode = new ContentNode();

        $menuItem = $this->createItem('sample_menu');
        $menuItem->setDisplay(false);
        $menuItem->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $contentNode);

        $this->menuContentNodesProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoContentNode(): void
    {
        $menuItem = $this->createItem('sample_menu');
        $menuItem->setDisplay(true);

        $this->menuContentNodesProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNotResolved(): void
    {
        $contentNode = new ContentNodeStub(42);

        $maxNestingLevel = 6;
        $menuItem = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $menuItem->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                ],
            ]
        );

        $this->menuContentNodesProvider
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($contentNode, ['tree_depth' => $maxTraverseLevel])
            ->willReturn(null);

        $this->builder->build($menuItem);

        self::assertEquals(
            [
                'display' => true,
                'label' => 'sample_menu',
                'uri' => null,
                'extras' => [
                    ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel,
                ],
                'children' => [
                    'sample_menu_item' => [
                        'display' => false,
                        'label' => 'sample_menu_item',
                        'uri' => null,
                        'extras' => [
                            MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                        ],
                        'children' => [],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menuItem)
        );
    }

    public function testBuildWhenNoChildren(): void
    {
        $contentNode = new ContentNodeStub(42);

        $maxNestingLevel = 6;
        $menuItem = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $menuItem->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                ],
            ]
        );

        $resolvedContentNode = $this->createResolvedNode(42, 'Root');
        $this->menuContentNodesProvider
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($contentNode, ['tree_depth' => $maxTraverseLevel])
            ->willReturn($resolvedContentNode);

        $this->builder->build($menuItem);

        self::assertEquals(
            [
                'display' => true,
                'label' => 'sample_menu',
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'children' => [
                    'sample_menu_item' => [
                        'display' => true,
                        'label' => (string)$resolvedContentNode->getTitles()[0],
                        'uri' => 'node/' . $resolvedContentNode->getId(),
                        'extras' => [
                            MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                        ],
                        'children' => [],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menuItem)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenHasChildren(): void
    {
        $contentNode = new ContentNodeStub(42);
        $resolvedContentNode = $this->createResolvedNode(
            42,
            'Root',
            [
                $this->createResolvedNode(11, 'Node 1'),
                $this->createResolvedNode(12, 'Node 2', [$this->createResolvedNode(121, 'Node 21')]),
                $this->createResolvedNode(13, 'Node 3'),
            ]
        );

        $maxNestingLevel = 6;
        $menuItem = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $menuItem->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                ],
            ]
        );

        $this->menuContentNodesProvider
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($contentNode, ['tree_depth' => $maxTraverseLevel])
            ->willReturn($resolvedContentNode);

        $this->entityManager
            ->expects(self::exactly(4))
            ->method('getReference')
            ->willReturnCallback(static fn ($class, $id) => new ProxyStub($class, $id));

        $this->builder->build($menuItem);

        self::assertEquals(
            [
                'display' => true,
                'label' => 'sample_menu',
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'children' => [
                    'sample_menu_item' => [
                        'display' => true,
                        'label' => 'Root',
                        'uri' => 'node/42',
                        'extras' => [
                            MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                        ],
                        'children' => [
                            'sample_menu_item_11' => [
                                'display' => true,
                                'label' => 'Node 1',
                                'uri' => 'node/11',
                                'extras' => [
                                    'isAllowed' => true,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 11),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 4,
                                ],
                                'children' => [],
                            ],
                            'sample_menu_item_12' => [
                                'display' => true,
                                'label' => 'Node 2',
                                'uri' => 'node/12',
                                'extras' => [
                                    'isAllowed' => true,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 12),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 4,
                                ],
                                'children' => [
                                    'sample_menu_item_121' => [
                                        'display' => true,
                                        'label' => 'Node 21',
                                        'uri' => 'node/121',
                                        'extras' => [
                                            'isAllowed' => true,
                                            'translate_disabled' => true,
                                            MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 121),
                                            MenuUpdate::MAX_TRAVERSE_LEVEL => 3,
                                        ],
                                        'children' => [],
                                    ],
                                ],
                            ],
                            'sample_menu_item_13' => [
                                'display' => true,
                                'label' => 'Node 3',
                                'uri' => 'node/13',
                                'extras' => [
                                    'isAllowed' => true,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 13),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 4,
                                ],
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menuItem)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenRestrictedByMaxNestingLevel(): void
    {
        $contentNode = new ContentNodeStub(42);
        $resolvedContentNode = $this->createResolvedNode(
            42,
            'Root',
            [
                $this->createResolvedNode(11, 'Node 1'),
                $this->createResolvedNode(12, 'Node 2', [$this->createResolvedNode(121, 'Node 21')]),
                $this->createResolvedNode(13, 'Node 3'),
            ]
        );

        $maxNestingLevel = 3;
        $menuItem = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $menuItem->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => 5,
                ],
            ]
        );

        $this->menuContentNodesProvider
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($contentNode, ['tree_depth' => $maxNestingLevel - 1])
            ->willReturn($resolvedContentNode);

        $this->entityManager
            ->expects(self::exactly(4))
            ->method('getReference')
            ->willReturnCallback(static fn ($class, $id) => new ProxyStub($class, $id));

        $this->builder->build($menuItem);

        self::assertEquals(
            [
                'display' => true,
                'label' => 'sample_menu',
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'children' => [
                    'sample_menu_item' => [
                        'display' => true,
                        'label' => 'Root',
                        'uri' => 'node/42',
                        'extras' => [
                            MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 1,
                        ],
                        'children' => [
                            'sample_menu_item_11' => [
                                'display' => true,
                                'label' => 'Node 1',
                                'uri' => 'node/11',
                                'extras' => [
                                    'isAllowed' => true,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 11),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 1,
                                ],
                                'children' => [],
                            ],
                            'sample_menu_item_12' => [
                                'display' => true,
                                'label' => 'Node 2',
                                'uri' => 'node/12',
                                'extras' => [
                                    'isAllowed' => true,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 12),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 1,
                                ],
                                'children' => [
                                    'sample_menu_item_121' => [
                                        'display' => true,
                                        'label' => 'Node 21',
                                        'uri' => 'node/121',
                                        'extras' => [
                                            'isAllowed' => true,
                                            'translate_disabled' => true,
                                            MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 121),
                                            MenuUpdate::MAX_TRAVERSE_LEVEL => 0,
                                        ],
                                        'children' => [],
                                    ],
                                ],
                            ],
                            'sample_menu_item_13' => [
                                'display' => true,
                                'label' => 'Node 3',
                                'uri' => 'node/13',
                                'extras' => [
                                    'isAllowed' => true,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 13),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 1,
                                ],
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menuItem)
        );
    }

    private function normalizeMenuItem(ItemInterface $menuItem): array
    {
        $result = [
            'display' => $menuItem->isDisplayed(),
            'label' => $menuItem->getLabel(),
            'uri' => $menuItem->getUri(),
            'extras' => $menuItem->getExtras(),
        ];

        $result['children'] = [];
        foreach ($menuItem->getChildren() as $childMenuItem) {
            $result['children'][$childMenuItem->getName()] = $this->normalizeMenuItem($childMenuItem);
        }

        return $result;
    }

    private function createResolvedNode(
        int $id,
        string $title,
        array $childNodes = []
    ): ResolvedContentNode {
        $resolvedNode = new ResolvedContentNode(
            $id,
            'sample_identifier_' . $id,
            $id,
            new ArrayCollection([(new LocalizedFallbackValue())->setString($title)]),
            (new ResolvedContentVariant())->addLocalizedUrl((new LocalizedFallbackValue())->setString('node/' . $id))
        );

        foreach ($childNodes as $childNode) {
            $resolvedNode->addChildNode($childNode);
        }

        return $resolvedNode;
    }
}
