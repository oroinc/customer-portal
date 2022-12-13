<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\Util\MenuManipulator;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Menu\MenuCategoriesProviderInterface;
use Oro\Bundle\CatalogBundle\Provider\MasterCatalogRootProviderInterface;
use Oro\Bundle\CatalogBundle\Tests\Unit\Stub\CategoryStub;
use Oro\Bundle\CommerceMenuBundle\Builder\MasterCatalogNavigationRootBuilder;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\PlatformBundle\Tests\Unit\Stub\ProxyStub;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\UserInterface;

class MasterCatalogNavigationRootBuilderTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private const MENU_TEMPLATE = 'template1';

    private MasterCatalogRootProviderInterface|\PHPUnit\Framework\MockObject\MockObject $masterCatalogRootProvider;

    private MenuCategoriesProviderInterface|\PHPUnit\Framework\MockObject\MockObject $menuCategoriesProvider;

    private TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject $tokenAccessor;

    private MasterCatalogNavigationRootBuilder $builder;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->masterCatalogRootProvider = $this->createMock(MasterCatalogRootProviderInterface::class);
        $this->menuCategoriesProvider = $this->createMock(MenuCategoriesProviderInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $menuTemplatesProvider = $this->createMock(MenuTemplatesProvider::class);

        $this->builder = new MasterCatalogNavigationRootBuilder(
            $managerRegistry,
            $this->tokenAccessor,
            $this->masterCatalogRootProvider,
            $this->menuCategoriesProvider,
            $menuTemplatesProvider
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
        $this->masterCatalogRootProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->menuCategoriesProvider
            ->expects(self::never())
            ->method(self::anything());

        $menu = $this->createItem('sample_menu');
        $menu->setDisplay(false);
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    public function testBuildItemNoCategories(): void
    {
        $rootCategory = new Category();
        $this->masterCatalogRootProvider->expects(self::once())
            ->method('getMasterCatalogRoot')
            ->willReturn($rootCategory);

        $user = $this->createMock(UserInterface::class);
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);
        $this->menuCategoriesProvider->expects(self::once())
            ->method('getCategories')
            ->with($rootCategory, $user, null, ['tree_depth' => 1])
            ->willReturn([]);

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenManyCategories(): void
    {
        $category1Data = [
            'id' => 1,
            'parentId' => 0,
            'title' => 'Category 1',
            'level' => 0,
        ];
        $category12Data = [
            'id' => 12,
            'parentId' => 1,
            'title' => 'Category 1-2',
            'level' => 1,
        ];
        $category13Data = [
            'id' => 13,
            'parentId' => 1,
            'title' => 'Category 1-3',
            'level' => 1,
        ];

        $user = $this->createMock(UserInterface::class);
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $category1 = (new CategoryStub())->setId(1);
        $this->masterCatalogRootProvider
            ->expects(self::once())
            ->method('getMasterCatalogRoot')
            ->willReturn($category1);

        $this->menuCategoriesProvider->expects(self::once())
            ->method('getCategories')
            ->with($category1, $user, null, ['tree_depth' => 1])
            ->willReturn([$category1Data, $category12Data, $category13Data]);

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu);

        $children = $menu->getChildren();

        self::assertNotEmpty($menu->getChildren());
        self::assertCount(2, $children);

        $menuManipulator = new MenuManipulator();
        self::assertEquals(
            [
                'name' => $menu->getName(),
                'label' => $menu->getLabel(),
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
                    'category_12' => [
                        'name' => 'category_12',
                        'label' => $category12Data['title'],
                        'uri' => null,
                        'attributes' => [],
                        'labelAttributes' => [],
                        'linkAttributes' => [],
                        'childrenAttributes' => [],
                        'extras' => [
                            'isAllowed' => true,
                            'category' => new ProxyStub(Category::class, $category12Data['id']),
                            'position' => -101,
                            'menu_template' => self::MENU_TEMPLATE,
                            'max_traverse_level' => 0,
                            'max_traverse_level_disabled' => false,
                            'translate_disabled' => true,
                        ],
                        'display' => true,
                        'displayChildren' => true,
                        'current' => null,
                        'children' => [],
                    ],
                    'category_13' => [
                        'name' => 'category_13',
                        'label' => $category13Data['title'],
                        'uri' => null,
                        'attributes' => [],
                        'labelAttributes' => [],
                        'linkAttributes' => [],
                        'childrenAttributes' => [],
                        'extras' => [
                            'isAllowed' => true,
                            'category' => new ProxyStub(Category::class, $category13Data['id']),
                            'position' => -102,
                            'menu_template' => self::MENU_TEMPLATE,
                            'max_traverse_level' => 0,
                            'max_traverse_level_disabled' => false,
                            'translate_disabled' => true,
                        ],
                        'display' => true,
                        'displayChildren' => true,
                        'current' => null,
                        'children' => [],
                    ],
                ],
            ],
            $menuManipulator->toArray($menu)
        );
    }
}
