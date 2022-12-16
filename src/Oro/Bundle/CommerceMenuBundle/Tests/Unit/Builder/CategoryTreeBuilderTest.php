<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Knp\Menu\Util\MenuManipulator;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Menu\MenuCategoriesProviderInterface;
use Oro\Bundle\CatalogBundle\Tests\Unit\Stub\CategoryStub;
use Oro\Bundle\CommerceMenuBundle\Builder\CategoryTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\PlatformBundle\Tests\Unit\Stub\ProxyStub;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CategoryTreeBuilderTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $managerRegistry;

    private UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject $urlGenerator;

    private MenuCategoriesProviderInterface|\PHPUnit\Framework\MockObject\MockObject $menuCategoriesProvider;

    private TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject $tokenAccessor;

    private CategoryTreeBuilder $builder;

    private EntityManager|\PHPUnit\Framework\MockObject\MockObject $entityManager;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->menuCategoriesProvider = $this->createMock(MenuCategoriesProviderInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->builder = new CategoryTreeBuilder(
            $managerRegistry,
            $this->urlGenerator,
            $this->menuCategoriesProvider,
            $this->tokenAccessor
        );

        $this->entityManager = $this->createMock(EntityManager::class);
        $managerRegistry
            ->expects(self::any())
            ->method('getManagerForClass')
            ->with(Category::class)
            ->willReturn($this->entityManager);
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects(self::once())
            ->method('isDisplayed')
            ->willReturn(false);
        $menuItem->expects(self::never())
            ->method('setUri');

        $this->menuCategoriesProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoCategory(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects(self::once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects(self::once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects(self::once())
            ->method('getExtra')
            ->with(MenuUpdate::TARGET_CATEGORY)
            ->willReturn(null);
        $menuItem->expects(self::never())
            ->method('setUri');

        $this->menuCategoriesProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenOneCategory(): void
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
        $menu->addChild(
            'category_1',
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
        $categoryData = ['id' => $category->getId(), 'parentId' => 0, 'title' => $title];
        $this->menuCategoriesProvider->expects(self::once())
            ->method('getCategories')
            ->with($category, $user, null, ['tree_depth' => $maxTraverseLevel])
            ->willReturn(
                [$category->getId() => $categoryData]
            );

        $this->builder->build($menu);

        self::assertEquals($title, $menu->getChild('category_1')->getLabel());
        self::assertEquals($url, $menu->getChild('category_1')->getUri());
        self::assertTrue($menu->getChild('category_1')->isDisplayed());
        self::assertEquals(
            [
                'category_data' => $categoryData,
                MenuUpdate::TARGET_CATEGORY => $category,
                MenuUpdate::MAX_TRAVERSE_LEVEL => $maxTraverseLevel,
            ],
            $menu->getChild('category_1')->getExtras()
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenCategoryWithChildren(): void
    {
        $category = (new CategoryStub())
            ->setId(1);

        $this->urlGenerator->expects(self::exactly(5))
            ->method('generate')
            ->willReturnCallback(static function ($routeName, array $parameters) {
                return $routeName . '/' . $parameters['categoryId'] . $parameters['includeSubcategories'];
            });

        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 5);
        $maxTraverseLevel = 2;
        $menu->addChild(
            'category_1',
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

        $category1Data = ['id' => $category->getId(), 'parentId' => 0, 'title' => 'Sample Category 1'];
        $category12Data = ['id' => 12, 'parentId' => 1, 'title' => 'Sample Category 1-2'];
        $category13Data = ['id' => 13, 'parentId' => 1, 'title' => 'Sample Category 1-3'];
        $category131Data = ['id' => 131, 'parentId' => 13, 'title' => 'Sample Category 1-3-1'];
        $category14Data = ['id' => 14, 'parentId' => 1, 'title' => 'Sample Category 1-4'];
        $categoriesData = [
            $category1Data['id'] => $category1Data,
            $category12Data['id'] => $category12Data,
            $category13Data['id'] => $category13Data,
            $category131Data['id'] => $category131Data,
            $category14Data['id'] => $category14Data,
        ];

        $this->menuCategoriesProvider->expects(self::once())
            ->method('getCategories')
            ->with($category, $user, null, ['tree_depth' => $maxTraverseLevel])
            ->willReturn($categoriesData);

        $this->entityManager
            ->expects(self::exactly(4))
            ->method('getReference')
            ->willReturnCallback(static fn ($class, $id) => new ProxyStub($class, $id));

        $this->builder->build($menu);

        $menuManipulator = new MenuManipulator();

        self::assertEquals(
            [
                'name' => 'sample_menu',
                'label' => 'sample_menu',
                'uri' => null,
                'attributes' => [],
                'labelAttributes' => [],
                'linkAttributes' => [],
                'childrenAttributes' => [],
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => 5],
                'display' => true,
                'displayChildren' => true,
                'current' => null,
                'children' => [
                    'category_1' => [
                        'name' => 'category_1',
                        'label' => $category1Data['title'],
                        'uri' => 'oro_product_frontend_product_index/11',
                        'attributes' => [],
                        'labelAttributes' => [],
                        'linkAttributes' => [],
                        'childrenAttributes' => [],
                        'extras' => [
                            MenuUpdate::TARGET_CATEGORY => $category,
                            MenuUpdate::MAX_TRAVERSE_LEVEL => 2,
                            'category_data' => $category1Data,
                        ],
                        'display' => true,
                        'displayChildren' => true,
                        'current' => null,
                        'children' => [
                            'category_1_12' => [
                                'name' => 'category_1_12',
                                'label' => $category12Data['title'],
                                'uri' => 'oro_product_frontend_product_index/121',
                                'attributes' => [],
                                'labelAttributes' => [],
                                'linkAttributes' => [],
                                'childrenAttributes' => [],
                                'extras' => [
                                    'isAllowed' => true,
                                    'category_data' => $category12Data,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category12Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 1,
                                ],
                                'display' => true,
                                'displayChildren' => true,
                                'current' => null,
                                'children' => [],
                            ],
                            'category_1_13' => [
                                'name' => 'category_1_13',
                                'label' => $category13Data['title'],
                                'uri' => 'oro_product_frontend_product_index/131',
                                'attributes' => [],
                                'labelAttributes' => [],
                                'linkAttributes' => [],
                                'childrenAttributes' => [],
                                'extras' => [
                                    'isAllowed' => true,
                                    'category_data' => $category13Data,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category13Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 1,
                                ],
                                'display' => true,
                                'displayChildren' => true,
                                'current' => null,
                                'children' => [
                                    'category_1_131' => [
                                        'name' => 'category_1_131',
                                        'label' => $category131Data['title'],
                                        'uri' => 'oro_product_frontend_product_index/1311',
                                        'attributes' => [],
                                        'labelAttributes' => [],
                                        'linkAttributes' => [],
                                        'childrenAttributes' => [],
                                        'extras' => [
                                            'isAllowed' => true,
                                            'category_data' => $category131Data,
                                            'translate_disabled' => true,
                                            MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                                Category::class,
                                                $category131Data['id']
                                            ),
                                            MenuUpdate::MAX_TRAVERSE_LEVEL => 0,
                                        ],
                                        'display' => true,
                                        'displayChildren' => true,
                                        'current' => null,
                                        'children' => [],
                                    ],
                                ],
                            ],
                            'category_1_14' => [
                                'name' => 'category_1_14',
                                'label' => $category14Data['title'],
                                'uri' => 'oro_product_frontend_product_index/141',
                                'attributes' => [],
                                'labelAttributes' => [],
                                'linkAttributes' => [],
                                'childrenAttributes' => [],
                                'extras' => [
                                    'isAllowed' => true,
                                    'category_data' => $category14Data,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category14Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 1,
                                ],
                                'display' => true,
                                'displayChildren' => true,
                                'current' => null,
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $menuManipulator->toArray($menu),
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
        $menu->addChild(
            'category_1',
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

        $category1Data = ['id' => $category->getId(), 'parentId' => 0, 'title' => 'Sample Category 1'];
        $category12Data = ['id' => 12, 'parentId' => 1, 'title' => 'Sample Category 1-2'];
        $category13Data = ['id' => 13, 'parentId' => 1, 'title' => 'Sample Category 1-3'];
        $category131Data = ['id' => 131, 'parentId' => 13, 'title' => 'Sample Category 1-3-1'];
        $category14Data = ['id' => 14, 'parentId' => 1, 'title' => 'Sample Category 1-4'];
        $categoriesData = [
            $category1Data['id'] => $category1Data,
            $category12Data['id'] => $category12Data,
            $category13Data['id'] => $category13Data,
            $category131Data['id'] => $category131Data,
            $category14Data['id'] => $category14Data,
        ];

        $this->menuCategoriesProvider->expects(self::once())
            ->method('getCategories')
            ->with($category, $user, null, ['tree_depth' => $maxNestingLevel - 1])
            ->willReturn($categoriesData);

        $this->entityManager
            ->expects(self::exactly(4))
            ->method('getReference')
            ->willReturnCallback(static fn ($class, $id) => new ProxyStub($class, $id));

        $this->builder->build($menu);

        $menuManipulator = new MenuManipulator();

        self::assertEquals(
            [
                'name' => 'sample_menu',
                'label' => 'sample_menu',
                'uri' => null,
                'attributes' => [],
                'labelAttributes' => [],
                'linkAttributes' => [],
                'childrenAttributes' => [],
                'extras' => [ConfigurationBuilder::MAX_NESTING_LEVEL => $maxNestingLevel],
                'display' => true,
                'displayChildren' => true,
                'current' => null,
                'children' => [
                    'category_1' => [
                        'name' => 'category_1',
                        'label' => $category1Data['title'],
                        'uri' => 'oro_product_frontend_product_index/11',
                        'attributes' => [],
                        'labelAttributes' => [],
                        'linkAttributes' => [],
                        'childrenAttributes' => [],
                        'extras' => [
                            MenuUpdate::TARGET_CATEGORY => $category,
                            MenuUpdate::MAX_TRAVERSE_LEVEL => 2,
                            'category_data' => $category1Data,
                        ],
                        'display' => true,
                        'displayChildren' => true,
                        'current' => null,
                        'children' => [
                            'category_1_12' => [
                                'name' => 'category_1_12',
                                'label' => $category12Data['title'],
                                'uri' => 'oro_product_frontend_product_index/121',
                                'attributes' => [],
                                'labelAttributes' => [],
                                'linkAttributes' => [],
                                'childrenAttributes' => [],
                                'extras' => [
                                    'isAllowed' => true,
                                    'category_data' => $category12Data,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category12Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 1,
                                ],
                                'display' => true,
                                'displayChildren' => true,
                                'current' => null,
                                'children' => [],
                            ],
                            'category_1_13' => [
                                'name' => 'category_1_13',
                                'label' => $category13Data['title'],
                                'uri' => 'oro_product_frontend_product_index/131',
                                'attributes' => [],
                                'labelAttributes' => [],
                                'linkAttributes' => [],
                                'childrenAttributes' => [],
                                'extras' => [
                                    'isAllowed' => true,
                                    'category_data' => $category13Data,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category13Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 1,
                                ],
                                'display' => true,
                                'displayChildren' => true,
                                'current' => null,
                                'children' => [
                                    'category_1_131' => [
                                        'name' => 'category_1_131',
                                        'label' => $category131Data['title'],
                                        'uri' => 'oro_product_frontend_product_index/1311',
                                        'attributes' => [],
                                        'labelAttributes' => [],
                                        'linkAttributes' => [],
                                        'childrenAttributes' => [],
                                        'extras' => [
                                            'isAllowed' => true,
                                            'category_data' => $category131Data,
                                            'translate_disabled' => true,
                                            MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                                Category::class,
                                                $category131Data['id']
                                            ),
                                            MenuUpdate::MAX_TRAVERSE_LEVEL => 0,
                                        ],
                                        'display' => true,
                                        'displayChildren' => true,
                                        'current' => null,
                                        'children' => [],
                                    ],
                                ],
                            ],
                            'category_1_14' => [
                                'name' => 'category_1_14',
                                'label' => $category14Data['title'],
                                'uri' => 'oro_product_frontend_product_index/141',
                                'attributes' => [],
                                'labelAttributes' => [],
                                'linkAttributes' => [],
                                'childrenAttributes' => [],
                                'extras' => [
                                    'isAllowed' => true,
                                    'category_data' => $category14Data,
                                    'translate_disabled' => true,
                                    MenuUpdate::TARGET_CATEGORY => new ProxyStub(
                                        Category::class,
                                        $category14Data['id']
                                    ),
                                    MenuUpdate::MAX_TRAVERSE_LEVEL => 1,
                                ],
                                'display' => true,
                                'displayChildren' => true,
                                'current' => null,
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $menuManipulator->toArray($menu),
        );
    }
}
