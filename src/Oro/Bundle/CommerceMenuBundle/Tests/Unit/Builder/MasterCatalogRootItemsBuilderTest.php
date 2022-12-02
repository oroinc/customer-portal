<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\Util\MenuManipulator;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Menu\MenuCategoriesProviderInterface;
use Oro\Bundle\CatalogBundle\Provider\MasterCatalogRootProviderInterface;
use Oro\Bundle\CatalogBundle\Tests\Unit\Stub\CategoryStub;
use Oro\Bundle\CommerceMenuBundle\Builder\MasterCatalogRootItemsBuilder;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Stub\ScopeStub;
use Oro\Bundle\NavigationBundle\Provider\MenuUpdateProvider;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\PlatformBundle\Tests\Unit\Stub\ProxyStub;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class MasterCatalogRootItemsBuilderTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private MasterCatalogRootProviderInterface|\PHPUnit\Framework\MockObject\MockObject $masterCatalogRootProvider;

    private MenuCategoriesProviderInterface|\PHPUnit\Framework\MockObject\MockObject $menuCategoriesProvider;

    private TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject $tokenAccessor;

    private WebCatalogProvider|\PHPUnit\Framework\MockObject\MockObject $webCatalogProvider;

    private MenuTemplatesProvider|\PHPUnit\Framework\MockObject\MockObject $menuTemplatesProvider;

    private MasterCatalogRootItemsBuilder $builder;

    private EntityManager|\PHPUnit\Framework\MockObject\MockObject $entityManager;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->masterCatalogRootProvider = $this->createMock(MasterCatalogRootProviderInterface::class);
        $this->menuCategoriesProvider = $this->createMock(MenuCategoriesProviderInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->webCatalogProvider = $this->createMock(WebCatalogProvider::class);
        $this->menuTemplatesProvider = $this->createMock(MenuTemplatesProvider::class);

        $this->builder = new MasterCatalogRootItemsBuilder(
            $managerRegistry,
            $this->tokenAccessor,
            $this->masterCatalogRootProvider,
            $this->menuCategoriesProvider,
            $this->webCatalogProvider,
            $this->menuTemplatesProvider
        );

        $this->entityManager = $this->createMock(EntityManager::class);
        $managerRegistry
            ->expects(self::any())
            ->method('getManagerForClass')
            ->with(Category::class)
            ->willReturn($this->entityManager);
    }

    public function testBuildWhenTargetMenuNotSet(): void
    {
        $this->webCatalogProvider
            ->expects(self::never())
            ->method('getWebCatalog')
            ->withAnyParameters();

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    public function testBuildWhenTargetMenuNotEquals(): void
    {
        $this->webCatalogProvider
            ->expects(self::never())
            ->method('getWebCatalog')
            ->withAnyParameters();

        $menu = $this->createItem('sample_menu');
        $this->builder->setTargetMenuName('target_menu');
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    /**
     * @dataProvider getBuildItemHasWebCatalogDataProvider
     */
    public function testBuildItemHasWebCatalog(array $options, ?Website $expectedWebsite): void
    {
        $webCatalog = new WebCatalog();
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->with($expectedWebsite)
            ->willReturn($webCatalog);

        $this->masterCatalogRootProvider->expects(self::never())
            ->method('getMasterCatalogRoot')
            ->withAnyParameters();

        $menu = $this->createItem('sample_menu');
        $this->builder->setTargetMenuName($menu->getName());
        $this->builder->build($menu, $options);

        self::assertEmpty($menu->getChildren());
    }

    public function getBuildItemHasWebCatalogDataProvider(): array
    {
        $website = new Website();

        return [
            'no website' => [
                'options' => [],
                'expectedWebsite' => null,
            ],
            'not website from [scopeContext][website]' => [
                'options' => [
                    MenuUpdateProvider::SCOPE_CONTEXT_OPTION => ['website' => new \stdClass()],
                ],
                'expectedWebsite' => null,
            ],
            'website from [scopeContext][website]' => [
                'options' => [
                    MenuUpdateProvider::SCOPE_CONTEXT_OPTION => ['website' => $website],
                ],
                'expectedWebsite' => $website,
            ],
            'website from [scopeContext] as Scope entity' => [
                'options' => [
                    MenuUpdateProvider::SCOPE_CONTEXT_OPTION => (new ScopeStub())->setWebsite($website),
                ],
                'expectedWebsite' => $website,
            ],
        ];
    }

    public function testBuildItemNoCategories(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->with(null)
            ->willReturn(null);

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
        $this->builder->setTargetMenuName($menu->getName());
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenManyCategories(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->with(null)
            ->willReturn(null);

        $this->entityManager
            ->expects(self::exactly(2))
            ->method('getReference')
            ->willReturnCallback(static fn ($class, $id) => new ProxyStub($class, $id));

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

        $menuTemplate = 'template1';
        $this->menuTemplatesProvider
            ->expects(self::once())
            ->method('getMenuTemplates')
            ->willReturn([
                $menuTemplate => ['label' => 'Template 1'],
                'template2' => ['label' => 'Template 2'],
            ]);

        $menu = $this->createItem('sample_menu');
        $this->builder->setTargetMenuName($menu->getName());
        $this->builder->build($menu);

        $children = $menu->getChildren();

        self::assertNotEmpty($menu->getChildren());
        self::assertCount(2, $children);

        $menuManipulator = new MenuManipulator();
        self::assertEquals([
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
                        'position' => -100,
                        'menu_template' => 'template1',
                        'max_traverse_level' => 0,
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
                        'position' => -100,
                        'menu_template' => 'template1',
                        'max_traverse_level' => 0,
                    ],
                    'display' => true,
                    'displayChildren' => true,
                    'current' => null,
                    'children' => [],
                ],
            ],
        ], $menuManipulator->toArray($menu), var_export($menuManipulator->toArray($menu), 1));
    }
}
