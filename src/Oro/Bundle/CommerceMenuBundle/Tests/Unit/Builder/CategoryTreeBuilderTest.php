<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Menu\MenuCategoriesProviderInterface;
use Oro\Bundle\CatalogBundle\Tests\Unit\Stub\CategoryStub;
use Oro\Bundle\CommerceMenuBundle\Builder\CategoryTreeBuilder;
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
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class CategoryTreeBuilderTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $managerRegistry;

    private UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject $urlGenerator;

    private MenuCategoriesProviderInterface|\PHPUnit\Framework\MockObject\MockObject $menuCategoriesProvider;

    private TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject $tokenAccessor;

    private CategoryTreeBuilder $builder;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->menuCategoriesProvider = $this->createMock(MenuCategoriesProviderInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $localizationHelper = $this->createMock(LocalizationHelper::class);

        $this->builder = new CategoryTreeBuilder(
            $managerRegistry,
            $this->urlGenerator,
            $this->menuCategoriesProvider,
            $this->tokenAccessor,
            $localizationHelper
        );

        $entityManager = $this->createMock(EntityManager::class);
        $managerRegistry
            ->expects(self::any())
            ->method('getManagerForClass')
            ->with(Category::class)
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

    public function testBuildWhenNoCategory(): void
    {
        $menuItem = $this->createItem('sample_menu');
        $menuItem->setDisplay(true);

        $this->menuCategoriesProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenIsLostItem(): void
    {
        $menu = $this->createItem('sample_menu');
        $menu->setDisplay(true);

        $this->menuCategoriesProvider
            ->expects(self::never())
            ->method(self::anything());

        $context = new MenuUpdateApplierContext($menu);
        $context->addLostItem($menu, $this->createMock(MenuUpdateInterface::class));
        $this->builder->onMenuUpdatesApplyAfter(new MenuUpdatesApplyAfterEvent($context));
        $this->builder->build($menu);
    }

    public function testBuildWhenNoCategoryData(): void
    {
        $category = (new CategoryStub())
            ->setId(1);

        $maxNestingLevel = 6;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $menuItem = $menu->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CATEGORY => $category,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                ],
            ]
        );

        $user = $this->createMock(UserInterface::class);
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->menuCategoriesProvider
            ->expects(self::once())
            ->method('getCategories')
            ->with($category, $user, ['tree_depth' => $maxTraverseLevel])
            ->willReturn([]);

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
                            MenuUpdate::TARGET_CATEGORY => $category,
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
        $category = (new CategoryStub())
            ->setId(1);

        $url = '/office-furniture';
        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(
                'oro_product_frontend_product_index',
                [
                    'categoryId' => $category->getId(),
                    'includeSubcategories' => true,
                ]
            )
            ->willReturn($url);

        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 5);
        $maxTraverseLevel = 2;
        $menuItem = $menu->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CATEGORY => $category,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                ],
            ]
        );

        $user = $this->createMock(UserInterface::class);
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $title = 'Sample Category';
        $categoryData = [
            'id' => $category->getId(),
            'parentId' => 0,
            'titles' => new ArrayCollection([$this->createTitle($title)])
        ];
        $this->menuCategoriesProvider->expects(self::once())
            ->method('getCategories')
            ->with($category, $user, ['tree_depth' => $maxTraverseLevel])
            ->willReturn(
                [$category->getId() => $categoryData]
            );

        $this->builder->build($menu);

        self::assertEquals($title, $menuItem->getLabel());
        self::assertEquals($url, $menuItem->getUri());
        self::assertTrue($menuItem->isDisplayed());
        self::assertEquals(
            [
                MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection($categoryData['titles']),
                MenuUpdate::TARGET_CATEGORY => new ProxyStub(Category::class, $category->getId()),
                MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                CategoryTreeBuilder::CATEGORY_DATA => $categoryData,
            ],
            $menuItem->getExtras()
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenHasChildren(): void
    {
        $category = (new CategoryStub())
            ->setId(1);

        $this->urlGenerator->expects(self::exactly(5))
            ->method('generate')
            ->willReturnCallback(static function ($routeName, array $parameters) {
                return $routeName . '/' . $parameters['categoryId'] . $parameters['includeSubcategories'];
            });

        $maxNestingLevel = 6;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $treeItemOptions = ['extras' => ['tree_item_option_key' => 'tree_item_option_value']];
        $menuItem = $menu->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CATEGORY => $category,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                    CategoryTreeBuilder::TREE_ITEM_OPTIONS => $treeItemOptions,
                ],
            ]
        );

        $user = $this->createMock(UserInterface::class);
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $category1Data = [
            'id' => $category->getId(),
            'parentId' => 0,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1')])
        ];
        $category12Data = [
            'id' => 12,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-2')])
        ];
        $category13Data = [
            'id' => 13,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-3')])
        ];
        $category131Data = [
            'id' => 131,
            'parentId' => 13,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-3-1')])
        ];
        $category14Data = [
            'id' => 14,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-4')])
        ];
        $categoriesData = [
            $category1Data['id'] => $category1Data,
            $category12Data['id'] => $category12Data,
            $category13Data['id'] => $category13Data,
            $category131Data['id'] => $category131Data,
            $category14Data['id'] => $category14Data,
        ];

        $this->menuCategoriesProvider
            ->expects(self::once())
            ->method('getCategories')
            ->with($category, $user, ['tree_depth' => $maxTraverseLevel])
            ->willReturn($categoriesData);

        $menuOptions = ['extras' => ['sample_key' => 'sample_value']];
        $this->builder->build($menu, $menuOptions);

        self::assertEquals(
            [
                'label' => $menu->getName(),
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'display' => true,
                'children' => [
                    $menuItem->getName() => [
                        'label' => (string)$category1Data['titles'][0],
                        'uri' => 'oro_product_frontend_product_index/11',
                        'extras' => [
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $category1Data['titles']
                            ),
                            MenuUpdate::TARGET_CATEGORY => new ProxyStub(Category::class, $category1Data['id']),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                            CategoryTreeBuilder::CATEGORY_DATA => $category1Data,
                            CategoryTreeBuilder::TREE_ITEM_OPTIONS => $treeItemOptions,
                        ],
                        'display' => true,
                        'children' => [
                            $this->getTreeItemName($menuItem, 12) => [
                                'label' => (string)$category12Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/121',
                                'extras' => [
                                    'sample_key' => 'sample_value',
                                    'tree_item_option_key' => 'tree_item_option_value',
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 0,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category12Data['titles']
                                    ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category12Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category12Data,
                                ],
                                'display' => true,
                                'children' => [],
                            ],
                            $this->getTreeItemName($menuItem, 13) => [
                                'label' => (string)$category13Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/131',
                                'extras' => [
                                    'sample_key' => 'sample_value',
                                    'tree_item_option_key' => 'tree_item_option_value',
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 1,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category13Data['titles']
                                    ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category13Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category13Data,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'display' => true,
                                'children' => [
                                    $this->getTreeItemName($menuItem, 131) => [
                                        'label' => (string)$category131Data['titles'][0],
                                        'uri' => 'oro_product_frontend_product_index/1311',
                                        'extras' => [
                                            'sample_key' => 'sample_value',
                                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                            MenuUpdateInterface::POSITION => 0,
                                            MenuUpdateInterface::TITLES =>
                                                LocalizedFallbackValueHelper::cloneCollection(
                                                    $category131Data['titles']
                                                ),
                                            MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                                Category::class,
                                                $category131Data['id']
                                            ),
                                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 2,
                                            CategoryTreeBuilder::CATEGORY_DATA => $category131Data,
                                            CategoryTreeBuilder::IS_TREE_ITEM => true,
                                        ],
                                        'display' => true,
                                        'children' => [],
                                    ],
                                ],
                            ],
                            $this->getTreeItemName($menuItem, 14) => [
                                'label' => (string)$category14Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/141',
                                'extras' => [
                                    'tree_item_option_key' => 'tree_item_option_value',
                                    'sample_key' => 'sample_value',
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 2,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category14Data['titles']
                                    ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category14Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category14Data,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'display' => true,
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menu),
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenRestrictedByMaxNestingLevel(): void
    {
        $category = (new CategoryStub())
            ->setId(1);

        $this->urlGenerator->expects(self::exactly(5))
            ->method('generate')
            ->willReturnCallback(static function ($routeName, array $parameters) {
                return $routeName . '/' . $parameters['categoryId'] . $parameters['includeSubcategories'];
            });

        $maxNestingLevel = 3;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $menuItem = $menu->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CATEGORY => $category,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => 5,
                ],
            ]
        );

        $user = $this->createMock(UserInterface::class);
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $category1Data = [
            'id' => $category->getId(),
            'parentId' => 0,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1')])
        ];
        $category12Data = [
            'id' => 12,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-2')])
        ];
        $category13Data = [
            'id' => 13,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-3')])
        ];
        $category131Data = [
            'id' => 131,
            'parentId' => 13,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-3-1')])
        ];
        $category14Data = [
            'id' => 14,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-4')])
        ];
        $categoriesData = [
            $category1Data['id'] => $category1Data,
            $category12Data['id'] => $category12Data,
            $category13Data['id'] => $category13Data,
            $category131Data['id'] => $category131Data,
            $category14Data['id'] => $category14Data,
        ];

        $this->menuCategoriesProvider->expects(self::once())
            ->method('getCategories')
            ->with($category, $user, ['tree_depth' => $maxNestingLevel - 1])
            ->willReturn($categoriesData);

        $this->builder->build($menu);

        self::assertEquals(
            [
                'label' => $menu->getName(),
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'display' => true,
                'children' => [
                    $menuItem->getName() => [
                        'label' => (string)$category1Data['titles'][0],
                        'uri' => 'oro_product_frontend_product_index/11',
                        'extras' => [
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $category1Data['titles']
                            ),
                            MenuUpdate::TARGET_CATEGORY => new ProxyStub(Category::class, $category1Data['id']),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 1,
                            CategoryTreeBuilder::CATEGORY_DATA => $category1Data,
                        ],
                        'display' => true,
                        'children' => [
                            $this->getTreeItemName($menuItem, 12) => [
                                'label' => (string)$category12Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/121',
                                'extras' => [
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 0,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category12Data['titles']
                                    ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category12Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 2,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category12Data,
                                ],
                                'display' => true,
                                'children' => [],
                            ],
                            $this->getTreeItemName($menuItem, 13) => [
                                'label' => (string)$category13Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/131',
                                'extras' => [
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 1,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category13Data['titles']
                                    ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category13Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 2,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category13Data,
                                ],
                                'display' => true,
                                'children' => [
                                    $this->getTreeItemName($menuItem, 131) => [
                                        'label' => (string)$category131Data['titles'][0],
                                        'uri' => 'oro_product_frontend_product_index/1311',
                                        'extras' => [
                                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                            MenuUpdateInterface::POSITION => 0,
                                            MenuUpdateInterface::TITLES =>
                                                LocalizedFallbackValueHelper::cloneCollection(
                                                    $category131Data['titles']
                                                ),
                                            MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                                Category::class,
                                                $category131Data['id']
                                            ),
                                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 3,
                                            CategoryTreeBuilder::IS_TREE_ITEM => true,
                                            CategoryTreeBuilder::CATEGORY_DATA => $category131Data,
                                        ],
                                        'display' => true,
                                        'children' => [],
                                    ],
                                ],
                            ],
                            $this->getTreeItemName($menuItem, 14) => [
                                'label' => (string)$category14Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/141',
                                'extras' => [
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 2,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category14Data['titles']
                                    ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category14Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxNestingLevel - 2,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category14Data,
                                ],
                                'display' => true,
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menu),
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenHasChildrenAndLost(): void
    {
        $category = (new CategoryStub())
            ->setId(1);
        $category13 = (new CategoryStub())
            ->setId(13);

        $this->urlGenerator->expects(self::exactly(4))
            ->method('generate')
            ->willReturnCallback(static function ($routeName, array $parameters) {
                return $routeName . '/' . $parameters['categoryId'] . $parameters['includeSubcategories'];
            });

        $maxNestingLevel = 6;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $menuItem = $menu->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CATEGORY => $category,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                ],
            ]
        );

        $user = $this->createMock(UserInterface::class);
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $category1Data = [
            'id' => $category->getId(),
            'parentId' => 0,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1')])
        ];
        $category12Data = [
            'id' => 12,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-2')])
        ];
        $category13Data = [
            'id' => 13,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-3')])
        ];
        $category131Data = [
            'id' => 131,
            'parentId' => 13,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-3-1')])
        ];
        $category14Data = [
            'id' => 14,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-4')])
        ];
        $categoriesData = [
            $category1Data['id'] => $category1Data,
            $category12Data['id'] => $category12Data,
            $category13Data['id'] => $category13Data,
            $category131Data['id'] => $category131Data,
            $category14Data['id'] => $category14Data,
        ];

        $this->menuCategoriesProvider->expects(self::once())
            ->method('getCategories')
            ->with($category, $user, ['tree_depth' => $maxTraverseLevel])
            ->willReturn($categoriesData);

        $lostItemName = $this->getTreeItemName($menuItem, 13);
        $lostItemMaxTraverseLevel = 0;
        $lostItem = $menuItem->addChild(
            $lostItemName,
            [
                'extras' => [
                    MenuUpdateInterface::POSITION => 42,
                    MenuUpdate::TARGET_CATEGORY => $category13,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $lostItemMaxTraverseLevel
                ]
            ]
        );

        $context = new MenuUpdateApplierContext($menu);
        $context->addLostItem($lostItem, $this->createMock(MenuUpdateInterface::class));
        $this->builder->onMenuUpdatesApplyAfter(new MenuUpdatesApplyAfterEvent($context));

        $this->builder->build($menu);

        self::assertEquals(
            [
                'label' => $menu->getName(),
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'display' => true,
                'children' => [
                    $menuItem->getName() => [
                        'label' => (string)$category1Data['titles'][0],
                        'uri' => 'oro_product_frontend_product_index/11',
                        'extras' => [
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $category1Data['titles']
                            ),
                            MenuUpdate::TARGET_CATEGORY => new ProxyStub(Category::class, $category1Data['id']),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                            CategoryTreeBuilder::CATEGORY_DATA => $category1Data,
                        ],
                        'display' => true,
                        'children' => [
                            $this->getTreeItemName($menuItem, 12) => [
                                'label' => (string)$category12Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/121',
                                'extras' => [
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 0,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category12Data['titles']
                                    ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category12Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category12Data,
                                ],
                                'display' => true,
                                'children' => [],
                            ],
                            $lostItemName => [
                                'label' => (string)$category13Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/131',
                                'extras' => [
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category13Data['titles']
                                    ),
                                    MenuUpdateInterface::POSITION => 42,
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category13Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $lostItemMaxTraverseLevel,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category13Data,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'display' => true,
                                'children' => [],
                            ],
                            $this->getTreeItemName($menuItem, 14) => [
                                'label' => (string)$category14Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/141',
                                'extras' => [
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 2,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category14Data['titles']
                                    ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category14Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category14Data,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'display' => true,
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menu),
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenHasChildrenAndSynthetic(): void
    {
        $category = (new CategoryStub())
            ->setId(1);
        $category13 = (new CategoryStub())
            ->setId(13);

        $this->urlGenerator->expects(self::exactly(5))
            ->method('generate')
            ->willReturnCallback(static function ($routeName, array $parameters) {
                return $routeName . '/' . $parameters['categoryId'] . $parameters['includeSubcategories'];
            });

        $maxNestingLevel = 6;
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, $maxNestingLevel);
        $maxTraverseLevel = 5;
        $category13MaxTraverseLevel = 5;
        $menuItem = $menu->addChild(
            'sample_menu_item',
            [
                'extras' => [
                    MenuUpdate::TARGET_CATEGORY => $category,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                ],
            ]
        );

        $user = $this->createMock(UserInterface::class);
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $category1Data = [
            'id' => $category->getId(),
            'parentId' => 0,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1')])
        ];
        $category12Data = [
            'id' => 12,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-2')])
        ];
        $category13Data = [
            'id' => 13,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-3')])
        ];
        $category131Data = [
            'id' => 131,
            'parentId' => 13,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-3-1')])
        ];
        $category14Data = [
            'id' => 14,
            'parentId' => 1,
            MenuUpdateInterface::TITLES => new ArrayCollection([$this->createTitle('Sample Category 1-4')])
        ];
        $categoriesData = [
            $category1Data['id'] => $category1Data,
            $category12Data['id'] => $category12Data,
            $category13Data['id'] => $category13Data,
            $category131Data['id'] => $category131Data,
            $category14Data['id'] => $category14Data,
        ];

        $this->menuCategoriesProvider->expects(self::exactly(2))
            ->method('getCategories')
            ->withConsecutive(
                [$category, $user, ['tree_depth' => $maxTraverseLevel]],
                [$category13, $user, ['tree_depth' => $category13MaxTraverseLevel]]
            )
            ->willReturnOnConsecutiveCalls(
                $categoriesData,
                [$category13Data['id'] => $category13Data, $category131Data['id'] => $category131Data]
            );

        $syntheticItemName = $this->getTreeItemName($menuItem, 13);
        $syntheticItem = $menu->addChild(
            $syntheticItemName,
            [
                'extras' => [
                    MenuUpdateInterface::IS_SYNTHETIC => true,
                    MenuUpdate::TARGET_CATEGORY => $category13,
                    MenuUpdate::MAX_TRAVERSE_LEVEL => $category13MaxTraverseLevel,
                ]
            ]
        );

        $context = new MenuUpdateApplierContext($menu);
        $context->addCreatedItem($syntheticItem, $this->createMock(MenuUpdateInterface::class));
        $this->builder->onMenuUpdatesApplyAfter(new MenuUpdatesApplyAfterEvent($context));
        $this->builder->build($menu);

        self::assertEquals(
            [
                'label' => $menu->getName(),
                'uri' => null,
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'display' => true,
                'children' => [
                    $menuItem->getName() => [
                        'label' => (string)$category1Data['titles'][0],
                        'uri' => 'oro_product_frontend_product_index/11',
                        'extras' => [
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $category1Data['titles']
                            ),
                            MenuUpdate::TARGET_CATEGORY => new ProxyStub(Category::class, $category1Data['id']),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
                            CategoryTreeBuilder::CATEGORY_DATA => $category1Data,
                        ],
                        'display' => true,
                        'children' => [
                            $this->getTreeItemName($menuItem, 12) => [
                                'label' => (string)$category12Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/121',
                                'extras' => [
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 0,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category12Data['titles']
                                    ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category12Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category12Data,
                                ],
                                'display' => true,
                                'children' => [],
                            ],
                            $this->getTreeItemName($menuItem, 14) => [
                                'label' => (string)$category14Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/141',
                                'extras' => [
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 1,
                                    MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                        $category14Data['titles']
                                    ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category14Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel - 1,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category14Data,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'display' => true,
                                'children' => [],
                            ],
                        ],
                    ],
                    $syntheticItemName => [
                        'label' => (string)$category13Data['titles'][0],
                        'uri' => 'oro_product_frontend_product_index/131',
                        'extras' => [
                            MenuUpdateInterface::IS_SYNTHETIC => true,
                            MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                            MenuUpdateInterface::TITLES => LocalizedFallbackValueHelper::cloneCollection(
                                $category13Data['titles']
                            ),
                            MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                Category::class,
                                $category13Data['id']
                            ),
                            MenuUpdate::MAX_TRAVERSE_LEVEL => $category13MaxTraverseLevel,
                            CategoryTreeBuilder::CATEGORY_DATA => $category13Data,
                        ],
                        'display' => true,
                        'children' => [
                            $this->getTreeItemName($menuItem, 131) => [
                                'label' => (string)$category131Data['titles'][0],
                                'uri' => 'oro_product_frontend_product_index/1311',
                                'extras' => [
                                    MenuUpdateInterface::IS_TRANSLATE_DISABLED => true,
                                    MenuUpdateInterface::POSITION => 0,
                                    MenuUpdateInterface::TITLES =>
                                        LocalizedFallbackValueHelper::cloneCollection(
                                            $category131Data['titles']
                                        ),
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category131Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => $category13MaxTraverseLevel - 1,
                                    CategoryTreeBuilder::CATEGORY_DATA => $category131Data,
                                    CategoryTreeBuilder::IS_TREE_ITEM => true,
                                ],
                                'display' => true,
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menu),
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

    private function createTitle(string $title): LocalizedFallbackValue
    {
        return (new LocalizedFallbackValue())->setString($title);
    }

    private function getTreeItemName(ItemInterface $parentMenuItem, int $contentNodeId): string
    {
        $prefix = CategoryTreeBuilder::getTreeItemNamePrefix(
            $parentMenuItem,
            $parentMenuItem->getExtra(MenuUpdate::TARGET_CATEGORY)?->getId()
        );

        return $prefix . $contentNodeId;
    }
}
