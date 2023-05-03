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
use Oro\Bundle\LocaleBundle\Tools\LocalizedFallbackValueHelper;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Event\MenuUpdatesApplyAfterEvent;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Oro\Bundle\NavigationBundle\MenuUpdate\Applier\Model\MenuUpdateApplierContext;
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

        $localizationHelper
            ->expects(self::any())
            ->method('getLocalizedValue')
            ->willReturnCallback(static fn ($collection) => (string)($collection[0] ?? null));
    }

    public function testBuildWhenNoContentNode(): void
    {
        $menu = $this->createItem('sample_menu');
        $menu->setDisplay(true);

        $this->menuContentNodesProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menu);
    }

    public function testBuildWhenIsLostItem(): void
    {
        $menu = $this->createItem('sample_menu');
        $menu->setDisplay(true);

        $this->menuContentNodesProvider
            ->expects(self::never())
            ->method(self::anything());

        $context = new MenuUpdateApplierContext($menu);
        $context->addLostItem($menu, $this->createMock(MenuUpdateInterface::class));
        $this->builder->onMenuUpdatesApplyAfter(new MenuUpdatesApplyAfterEvent($context));
        $this->builder->build($menu);
    }

    public function testBuildWhenNotResolved(): void
    {
        $contentNode = new ContentNodeStub(42);

        $maxNestingLevel = 6;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $menuItem = $menu->addChild(
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

        $this->builder->build($menu);

        self::assertEquals(
            [
                'display' => true,
                'label' => $menu->getName(),
                'uri' => null,
                'extras' => [
                    ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel,
                ],
                'children' => [
                    $menuItem->getName() => [
                        'display' => false,
                        'label' => $menuItem->getName(),
                        'uri' => null,
                        'extras' => [
                            MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                        ],
                        'children' => [],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menu)
        );
    }

    public function testBuildWhenNoChildren(): void
    {
        $contentNode = new ContentNodeStub(42);

        $maxNestingLevel = 6;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $menuItem = $menu->addChild(
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

        $this->builder->build($menu);

        self::assertEquals(
            [
                'display' => true,
                'label' => $menu->getName(),
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'children' => [
                    $menuItem->getName() => [
                        'display' => true,
                        'label' => (string)$resolvedContentNode->getTitles()[0],
                        'uri' => 'node/' . $resolvedContentNode->getId(),
                        'extras' => [
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $resolvedContentNode->getTitles()
                            ),
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, $contentNode->getId()),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                        ],
                        'children' => [],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menu)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenHasChildren(): void
    {
        $contentNode = new ContentNodeStub(42);
        $resolvedContentNode11 = $this->createResolvedNode(11, 'Node 1');
        $resolvedContentNode121 = $this->createResolvedNode(121, 'Node 21');
        $resolvedContentNode12 = $this->createResolvedNode(12, 'Node 2', [$resolvedContentNode121]);
        $resolvedContentNode13 = $this->createResolvedNode(13, 'Node 3');
        $resolvedContentNode = $this->createResolvedNode(
            42,
            'Root',
            [
                $resolvedContentNode11,
                $resolvedContentNode12,
                $resolvedContentNode13,
            ]
        );

        $maxNestingLevel = 6;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $treeItemOptions = ['extras' => ['tree_item_option_key' => 'tree_item_option_value']];
        $menuItem = $menu->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                    ContentNodeTreeBuilder::TREE_ITEM_OPTIONS => $treeItemOptions,
                ],
            ]
        );

        $this->menuContentNodesProvider
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($contentNode, ['tree_depth' => $maxTraverseLevel])
            ->willReturn($resolvedContentNode);

        $menuOptions = ['extras' => ['sample_key' => 'sample_value']];
        $this->builder->build($menu, $menuOptions);

        self::assertEquals(
            [
                'display' => true,
                'label' => $menu->getName(),
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'children' => [
                    $menuItem->getName() => [
                        'display' => true,
                        'label' => 'Root',
                        'uri' => 'node/42',
                        'extras' => [
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $resolvedContentNode->getTitles()
                            ),
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, $contentNode->getId()),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                            ContentNodeTreeBuilder::TREE_ITEM_OPTIONS => $treeItemOptions,
                        ],
                        'children' => [
                            $this->getTreeItemName($menuItem, 11) => [
                                'display' => true,
                                'label' => (string)$resolvedContentNode11->getTitles()[0],
                                'uri' => 'node/11',
                                'extras' => [
                                    'sample_key' => 'sample_value',
                                    'tree_item_option_key' => 'tree_item_option_value',
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode11->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 0,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 11),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [],
                            ],
                            $this->getTreeItemName($menuItem, 12) => [
                                'display' => true,
                                'label' => 'Node 2',
                                'uri' => 'node/12',
                                'extras' => [
                                    'sample_key' => 'sample_value',
                                    'tree_item_option_key' => 'tree_item_option_value',
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode12->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 1,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 12),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [
                                    $this->getTreeItemName($menuItem, 121) => [
                                        'display' => true,
                                        'label' => 'Node 21',
                                        'uri' => 'node/121',
                                        'extras' => [
                                            'sample_key' => 'sample_value',
                                            MenuUpdateInterface::TITLES =>
                                                LocalizedFallbackValueHelper::cloneCollection(
                                                    $resolvedContentNode121->getTitles()
                                                ),
                                            MenuUpdateInterface::POSITION => 0,
                                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                            MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 121),
                                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 2,
                                            ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                        ],
                                        'children' => [],
                                    ],
                                ],
                            ],
                            $this->getTreeItemName($menuItem, 13) => [
                                'display' => true,
                                'label' => 'Node 3',
                                'uri' => 'node/13',
                                'extras' => [
                                    'sample_key' => 'sample_value',
                                    'tree_item_option_key' => 'tree_item_option_value',
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode13->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 2,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 13),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menu)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenRestrictedByMaxNestingLevel(): void
    {
        $contentNode = new ContentNodeStub(42);
        $resolvedContentNode11 = $this->createResolvedNode(11, 'Node 1');
        $resolvedContentNode121 = $this->createResolvedNode(121, 'Node 21');
        $resolvedContentNode12 = $this->createResolvedNode(12, 'Node 2', [$resolvedContentNode121]);
        $resolvedContentNode13 = $this->createResolvedNode(13, 'Node 3');
        $resolvedContentNode = $this->createResolvedNode(
            42,
            'Root',
            [
                $resolvedContentNode11,
                $resolvedContentNode12,
                $resolvedContentNode13,
            ]
        );

        $maxNestingLevel = 3;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $menuItem = $menu->addChild(
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

        $this->builder->build($menu);

        self::assertEquals(
            [
                'display' => true,
                'label' => $menu->getName(),
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'children' => [
                    $menuItem->getName() => [
                        'display' => true,
                        'label' => 'Root',
                        'uri' => 'node/42',
                        'extras' => [
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $resolvedContentNode->getTitles()
                            ),
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, $contentNode->getId()),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 1,
                        ],
                        'children' => [
                            $this->getTreeItemName($menuItem, 11) => [
                                'display' => true,
                                'label' => 'Node 1',
                                'uri' => 'node/11',
                                'extras' => [
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode11->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 0,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 11),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 2,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [],
                            ],
                            $this->getTreeItemName($menuItem, 12) => [
                                'display' => true,
                                'label' => 'Node 2',
                                'uri' => 'node/12',
                                'extras' => [
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode12->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 1,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 12),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 2,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [
                                    $this->getTreeItemName($menuItem, 121) => [
                                        'display' => true,
                                        'label' => 'Node 21',
                                        'uri' => 'node/121',
                                        'extras' => [
                                            MenuUpdateInterface::TITLES =>
                                                LocalizedFallbackValueHelper::cloneCollection(
                                                    $resolvedContentNode121->getTitles()
                                                ),
                                            MenuUpdateInterface::POSITION => 0,
                                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                            MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 121),
                                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 3,
                                            ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                        ],
                                        'children' => [],
                                    ],
                                ],
                            ],
                            $this->getTreeItemName($menuItem, 13) => [
                                'display' => true,
                                'label' => 'Node 3',
                                'uri' => 'node/13',
                                'extras' => [
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode13->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 2,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 13),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 2,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menu)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenHasChildrenAndLost(): void
    {
        $contentNode = new ContentNodeStub(42);
        $contentNode12 = new ContentNodeStub(12);
        $resolvedContentNode11 = $this->createResolvedNode(11, 'Node 1');
        $resolvedContentNode121 = $this->createResolvedNode(121, 'Node 21');
        $resolvedContentNode12 = $this->createResolvedNode(12, 'Node 2', [$resolvedContentNode121]);
        $resolvedContentNode13 = $this->createResolvedNode(13, 'Node 3');
        $resolvedContentNode = $this->createResolvedNode(
            42,
            'Root',
            [
                $resolvedContentNode11,
                $resolvedContentNode12,
                $resolvedContentNode13,
            ]
        );

        $maxNestingLevel = 6;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $menuItem = $menu->addChild(
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

        $lostItemName = $this->getTreeItemName($menuItem, 12);
        $lostItemMaxTraverseLevel = 0;
        $lostItem = $menuItem->addChild(
            $lostItemName,
            [
                'extras' => [
                    MenuUpdateInterface::POSITION => 42,
                    MenuUpdate::TARGET_CONTENT_NODE => $contentNode12,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $lostItemMaxTraverseLevel
                ]
            ]
        );

        $context = new MenuUpdateApplierContext($menu);
        $context->addLostItem($lostItem, $this->createMock(MenuUpdateInterface::class));
        $this->builder->onMenuUpdatesApplyAfter(new MenuUpdatesApplyAfterEvent($context));
        $this->builder->build($menu);

        self::assertFalse($context->isLostItem($lostItemName));

        self::assertEquals(
            [
                'display' => true,
                'label' => $menu->getName(),
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'children' => [
                    $menuItem->getName() => [
                        'display' => true,
                        'label' => 'Root',
                        'uri' => 'node/42',
                        'extras' => [
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $resolvedContentNode->getTitles()
                            ),
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, $contentNode->getId()),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                        ],
                        'children' => [
                            $this->getTreeItemName($menuItem, 11) => [
                                'display' => true,
                                'label' => (string)$resolvedContentNode11->getTitles()[0],
                                'uri' => 'node/11',
                                'extras' => [
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode11->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 0,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 11),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [],
                            ],
                            $lostItemName => [
                                'display' => true,
                                'label' => 'Node 2',
                                'uri' => 'node/12',
                                'extras' => [
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode12->getTitles()
                                    ),
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 42,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 12),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $lostItemMaxTraverseLevel,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [],
                            ],
                            $this->getTreeItemName($menuItem, 13) => [
                                'display' => true,
                                'label' => 'Node 3',
                                'uri' => 'node/13',
                                'extras' => [
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode13->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 2,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 13),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menu)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenHasChildrenAndSynthetic(): void
    {
        $contentNode = new ContentNodeStub(42);
        $contentNode12 = new ContentNodeStub(12);
        $resolvedContentNode11 = $this->createResolvedNode(11, 'Node 1');
        $resolvedContentNode121 = $this->createResolvedNode(121, 'Node 21');
        $resolvedContentNode12 = $this->createResolvedNode(12, 'Node 2', [$resolvedContentNode121]);
        $resolvedContentNode13 = $this->createResolvedNode(13, 'Node 3');
        $resolvedContentNode = $this->createResolvedNode(
            42,
            'Root',
            [
                $resolvedContentNode11,
                $resolvedContentNode12,
                $resolvedContentNode13,
            ]
        );

        $maxNestingLevel = 6;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $contentNode12MaxTraverseLevel = 5;
        $menuItem = $menu->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CONTENT_NODE => $contentNode,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                ],
            ]
        );

        $this->menuContentNodesProvider
            ->expects(self::exactly(2))
            ->method('getResolvedContentNode')
            ->withConsecutive(
                [$contentNode, ['tree_depth' => $maxTraverseLevel]],
                [$contentNode12, ['tree_depth' => $contentNode12MaxTraverseLevel]]
            )
            ->willReturnOnConsecutiveCalls($resolvedContentNode, $resolvedContentNode12);

        $syntheticItemName = $this->getTreeItemName($menuItem, 12);
        $syntheticItem = $menu->addChild(
            $syntheticItemName,
            [
                'extras' => [
                    MenuUpdateInterface::IS_SYNTHETIC => true,
                    MenuUpdate::TARGET_CONTENT_NODE => $contentNode12,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $contentNode12MaxTraverseLevel,
                ]
            ]
        );

        $context = new MenuUpdateApplierContext($menu);
        $context->addCreatedItem($syntheticItem, $this->createMock(MenuUpdateInterface::class));
        $this->builder->onMenuUpdatesApplyAfter(new MenuUpdatesApplyAfterEvent($context));
        $this->builder->build($menu);

        self::assertEquals(
            [
                'display' => true,
                'label' => $menu->getName(),
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'children' => [
                    $menuItem->getName() => [
                        'display' => true,
                        'label' => 'Root',
                        'uri' => 'node/42',
                        'extras' => [
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $resolvedContentNode->getTitles()
                            ),
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, $contentNode->getId()),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                        ],
                        'children' => [
                            $this->getTreeItemName($menuItem, 11) => [
                                'display' => true,
                                'label' => (string)$resolvedContentNode11->getTitles()[0],
                                'uri' => 'node/11',
                                'extras' => [
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode11->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 0,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 11),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [],
                            ],
                            $this->getTreeItemName($menuItem, 13) => [
                                'display' => true,
                                'label' => 'Node 3',
                                'uri' => 'node/13',
                                'extras' => [
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode13->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 1,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 13),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [],
                            ],
                        ],
                    ],
                    $this->getTreeItemName($menuItem, 12) => [
                        'display' => true,
                        'label' => 'Node 2',
                        'uri' => 'node/12',
                        'extras' => [
                            MenuUpdateInterface::IS_SYNTHETIC => true,
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $resolvedContentNode12->getTitles()
                            ),
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 12),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $contentNode12MaxTraverseLevel,
                        ],
                        'children' => [
                            $this->getTreeItemName($menuItem, 121) => [
                                'display' => true,
                                'label' => 'Node 21',
                                'uri' => 'node/121',
                                'extras' => [
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $resolvedContentNode121->getTitles()
                                    ),
                                    MenuUpdateInterface::POSITION => 0,
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdate::TARGET_CONTENT_NODE => new ProxyStub(ContentNode::class, 121),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $contentNode12MaxTraverseLevel - 1,
                                    ContentNodeTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menu)
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

    private function getTreeItemName(ItemInterface $parentMenuItem, int $contentNodeId): string
    {
        $prefix = ContentNodeTreeBuilder::getTreeItemNamePrefix(
            $parentMenuItem,
            $parentMenuItem->getExtra(MenuUpdate::TARGET_CONTENT_NODE)?->getId()
        );

        return $prefix . $contentNodeId;
    }
}
