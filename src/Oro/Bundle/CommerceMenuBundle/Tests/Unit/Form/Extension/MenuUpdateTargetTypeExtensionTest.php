<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Extension;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Form\Type\CategoryTreeType;
use Oro\Bundle\CatalogBundle\JsTree\CategoryTreeHandler;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Form\Extension\MenuUpdateTargetTypeExtension;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub\MenuUpdateTypeStub;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Oro\Bundle\FormBundle\Autocomplete\SearchRegistry;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\TooltipFormExtensionStub;
use Oro\Bundle\NavigationBundle\Form\Type\RouteChoiceType;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Oro\Bundle\NavigationBundle\Tests\Unit\Form\Type\Stub\RouteChoiceTypeStub;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\SecurityBundle\Util\UriSecurityHelper;
use Oro\Bundle\SecurityBundle\Validator\Constraints\NotDangerousProtocolValidator;
use Oro\Bundle\TranslationBundle\Form\Extension\TranslatableChoiceTypeExtension;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Form\Type\ContentNodeFromWebCatalogSelectType;
use Oro\Bundle\WebCatalogBundle\JsTree\ContentNodeTreeHandler;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MenuUpdateTargetTypeExtensionTest extends FormIntegrationTestCase
{
    use MenuItemTestTrait;

    private WebCatalogProvider|\PHPUnit\Framework\MockObject\MockObject $webCatalogProvider;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getExtensions(): array
    {
        $this->webCatalogProvider = $this->createMock(WebCatalogProvider::class);

        $entityManager = $this->createMock(EntityManager::class);
        $categoryEntityManager = $this->createMock(EntityManager::class);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects(self::any())
            ->method('getManagerForClass')
            ->willReturnMap([
                [ContentNode::class, $entityManager],
                [Category::class, $categoryEntityManager],
            ]);

        $classMetadata = new ClassMetadata(WebCatalog::class);
        $classMetadata->setIdentifier(['id']);
        $entityManager->expects(self::any())
            ->method('getClassMetadata')
            ->willReturn($classMetadata);

        $categoryClassMetadata = new ClassMetadata(Category::class);
        $categoryClassMetadata->setIdentifier(['id']);
        $categoryEntityManager->expects(self::any())
            ->method('getClassMetadata')
            ->willReturn($categoryClassMetadata);

        $repo = $this->createMock(EntityRepository::class);
        $repo->expects(self::any())
            ->method('find')
            ->willReturn($this->createMock(ContentNode::class));
        $entityManager->expects(self::any())
            ->method('getRepository')
            ->willReturn($repo);

        $categoryRepository = $this->createMock(EntityRepository::class);
        $categoryRepository->expects(self::any())
            ->method('find')
            ->willReturn($this->createMock(Category::class));
        $categoryEntityManager->expects(self::any())
            ->method('getRepository')
            ->willReturn($categoryRepository);

        $handler = $this->createMock(SearchHandlerInterface::class);
        $handler->expects(self::any())
            ->method('getProperties')
            ->willReturn([]);
        $handler->expects(self::any())
            ->method('getEntityName')
            ->willReturn('Test\Entity');

        $searchRegistry = $this->createMock(SearchRegistry::class);
        $searchRegistry->expects(self::any())
            ->method('getSearchHandler')
            ->willReturn($handler);

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects(self::any())
            ->method('getProvider')
            ->willReturn($configProvider = $this->getConfigProvider());

        return [
            new PreloadedExtension(
                [
                    new OroEntitySelectOrCreateInlineType(
                        $this->createMock(AuthorizationCheckerInterface::class),
                        $this->createMock(FeatureChecker::class),
                        $configManager,
                        $entityManager,
                        $searchRegistry
                    ),
                    new OroJquerySelect2HiddenType($entityManager, $searchRegistry, $configProvider),
                    new ContentNodeFromWebCatalogSelectType($this->createMock(ContentNodeTreeHandler::class)),
                    new EntityIdentifierType($managerRegistry),
                    RouteChoiceType::class => new RouteChoiceTypeStub(['sample_route' => 'sample_route']),
                    new CategoryTreeType($this->createMock(CategoryTreeHandler::class)),
                    EntityType::class => new EntityTypeStub(),
                ],
                [
                    MenuUpdateTypeStub::class => [new MenuUpdateTargetTypeExtension($this->webCatalogProvider)],
                    FormType::class => [new TooltipFormExtensionStub($this)],
                    ChoiceType::class => [new TranslatableChoiceTypeExtension()],
                ]
            ),
            $this->getValidatorExtension(true),
        ];
    }

    protected function getValidators(): array
    {
        return [
            'oro_security.validator.constraints.not_dangerous_protocol' =>
                new NotDangerousProtocolValidator(new UriSecurityHelper([])),
        ];
    }

    private function getConfigProvider(): ConfigProvider
    {
        $config = $this->createMock(Config::class);
        $config->expects(self::any())
            ->method('has')
            ->with('grid_name')
            ->willReturn(true);
        $config->expects(self::any())
            ->method('get')
            ->with('grid_name')
            ->willReturn('sample-grid');

        $configProvider = $this->createMock(ConfigProvider::class);
        $configProvider->expects(self::any())
            ->method('getConfig')
            ->willReturn($config);

        return $configProvider;
    }

    public function testBuildFormHasDefaultUriTargetTypeWhenNewMenuUpdateAndNoMenuItem(): void
    {
        $menuUpdate = new MenuUpdateStub();
        $menu = $this->createItem('sample_menu');

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        self::assertEquals(MenuUpdate::TARGET_URI, $form->get('targetType')->getData());
    }

    public function testBuildFormHasDefaultNoneTargetTypeWhenExistingMenuUpdate(): void
    {
        $menuUpdate = new MenuUpdateStub(42);
        $menu = $this->createItem('sample_menu');

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        self::assertEquals(MenuUpdate::TARGET_NONE, $form->get('targetType')->getData());
    }

    public function testBuildFormHasDefaultNoneTargetTypeWhenMenuItemExists(): void
    {
        $menuUpdate = new MenuUpdateStub();
        $menu = $this->createItem('sample_menu');
        $menuItem = $menu->addChild('sample_item');

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu, 'menu_item' => $menuItem]
        );

        self::assertEquals(MenuUpdate::TARGET_NONE, $form->get('targetType')->getData());
    }

    /**
     * @dataProvider submitTargetTypeWhenIsCustomDataProvider
     */
    public function testSubmitTargetTypeWhenIsCustom(array $submitData, MenuUpdate $expectedMenuUpdate): void
    {
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menuUpdate = new MenuUpdateStub();
        $menuUpdate->setCustom(true);
        $menu = $this->createItem('sample_menu');

        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate, ['menu' => $menu]);

        $this->assertFormOptionEqual(false, 'disabled', $form->get('targetType'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('contentNode'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('category'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('systemPageRoute'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('uri'));

        $form->submit($submitData);

        $this->assertFormIsValid($form);
        self::assertEquals($expectedMenuUpdate, $form->getData());
    }

    public function submitTargetTypeWhenIsCustomDataProvider(): array
    {
        $contentNode = $this->createMock(ContentNode::class);
        $category = $this->createMock(Category::class);

        return [
            'uri target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_URI,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setUri('sample/uri'),
            ],
            'system page target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_SYSTEM_PAGE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setSystemPageRoute('sample_route'),
            ],
            'content node target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CONTENT_NODE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setContentNode($contentNode),
            ],
            'category target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CATEGORY,
                    'uri' => 'sample/uri',
                    'category' => $category,
                    'contentNode' => $contentNode,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setCategory($category),
            ],
            'none target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_NONE,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true),
            ],
        ];
    }

    /**
     * @dataProvider submitTargetTypeWhenSystemButRootDataProvider
     */
    public function testSubmitTargetTypeWhenSystemButRoot(array $submitData, MenuUpdate $expectedMenuUpdate): void
    {
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menuUpdate = new MenuUpdateStub();
        $menu = $this->createItem('sample_menu');

        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate, ['menu' => $menu, 'menu_item' => $menu]);

        $this->assertFormOptionEqual(false, 'disabled', $form->get('targetType'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('contentNode'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('category'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('systemPageRoute'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('uri'));

        $form->submit($submitData);

        $this->assertFormIsValid($form);
        self::assertEquals($expectedMenuUpdate, $form->getData());
    }

    public function submitTargetTypeWhenSystemButRootDataProvider(): array
    {
        $contentNode = $this->createMock(ContentNode::class);
        $category = $this->createMock(Category::class);

        return [
            'uri target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_URI,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setUri('sample/uri'),
            ],
            'system page target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_SYSTEM_PAGE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setSystemPageRoute('sample_route'),
            ],
            'content node target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CONTENT_NODE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setContentNode($contentNode),
            ],
            'category target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CATEGORY,
                    'uri' => 'sample/uri',
                    'category' => $category,
                    'contentNode' => $contentNode,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCategory($category),
            ],
            'none target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_NONE,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub()),
            ],
        ];
    }

    /**
     * @dataProvider submitTargetTypeWhenIsSystemDataProvider
     */
    public function testSubmitTargetTypeWhenIsSystem(array $submitData): void
    {
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menuUpdate = new MenuUpdateStub();
        $menu = $this->createItem('sample_menu');
        $menu->addChild('sample_item');

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu, 'menu_item' => $menu->getChild('sample_item')]
        );

        $this->assertFormOptionEqual(true, 'disabled', $form->get('targetType'));
        $this->assertFormOptionEqual(true, 'disabled', $form->get('contentNode'));
        $this->assertFormOptionEqual(true, 'disabled', $form->get('category'));
        $this->assertFormOptionEqual(true, 'disabled', $form->get('systemPageRoute'));
        $this->assertFormOptionEqual(true, 'disabled', $form->get('uri'));

        $form->submit($submitData);

        $this->assertFormIsValid($form);
        self::assertEquals(new MenuUpdateStub(), $form->getData());
    }

    public function submitTargetTypeWhenIsSystemDataProvider(): array
    {
        $contentNode = $this->createMock(ContentNode::class);
        $category = $this->createMock(Category::class);

        return [
            'uri target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_URI,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                ],
            ],
            'system page target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_SYSTEM_PAGE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                ],
            ],
            'content node target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CONTENT_NODE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                ],
            ],
            'category target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CATEGORY,
                    'uri' => 'sample/uri',
                    'category' => $category,
                    'contentNode' => $contentNode,
                ],
            ],
            'none target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_NONE,
                ],
            ],
        ];
    }

    public function testBuildHasMinTraverseLevelWhenRootMenuItem(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menuUpdate = new MenuUpdateStub();

        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 3);

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu, 'menu_item' => $menu]
        );

        $this->assertFormOptionEqual([1 => 1, 2 => 2, 3 => 3], 'choices', $form->get('maxTraverseLevel'));
    }

    public function testBuildHasDefaultMaxTraverseLevelWhenRootMenuItemHasNoMaxNestingLevel(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menuUpdate = new MenuUpdateStub();

        $menu = $this->createItem('sample_menu');

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu, 'menu_item' => $menu]
        );

        $this->assertFormOptionEqual(
            [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6],
            'choices',
            $form->get('maxTraverseLevel')
        );
    }

    public function testSubmitWhenMaxTraverseLevelIsDisabledDueToMaxNestingLevel(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menuUpdate = new MenuUpdateStub();
        $menuUpdate->setMaxTraverseLevel(3);

        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 1);
        $menuItem = $menu->addChild('sample_item');

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu_item' => $menuItem, 'menu' => $menu]
        );

        $this->assertFormOptionEqual(true, 'disabled', $form->get('maxTraverseLevel'));

        $form->submit([
            'maxTraverseLevel' => 5,
        ]);

        $expected = (new MenuUpdateStub())
            ->setMaxTraverseLevel($menuUpdate->getMaxTraverseLevel());

        $this->assertFormIsValid($form);

        self::assertEquals($expected, $form->getData());
    }

    public function testSubmitWhenMaxTraverseLevelIsNotRestrictedByParentBecauseNoMenuItem(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 6);

        $menuItem1 = $menu->addChild('sample_item_1');
        $menuItem11 = $menuItem1->addChild('sample_item_1_11')
            ->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 3);

        $menuUpdate = (new MenuUpdateStub())
            ->setKey('new_item')
            ->setParentKey($menuItem11->getName())
            ->setMaxTraverseLevel(5);

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        $this->assertFormOptionEqual([0, 1, 2, 3], 'choices', $form->get('maxTraverseLevel'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('maxTraverseLevel'));

        $form->submit([
            'maxTraverseLevel' => 2,
        ]);

        $this->assertFormIsValid($form);

        $expected = (new MenuUpdateStub())
            ->setKey($menuUpdate->getKey())
            ->setParentKey($menuItem11->getName())
            ->setMaxTraverseLevel(2);

        self::assertEquals($expected, $form->getData());
    }

    public function testSubmitWhenMaxTraverseLevelIsNotRestrictedByParentBecauseCustom(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 6);

        $menuItem1 = $menu->addChild('sample_item_1');
        $menuItem11 = $menuItem1->addChild('sample_item_1_11')
            ->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 3);
        $menuItem11Custom = $menuItem11->addChild('sample_item_1_11_custom');

        $menuUpdate = (new MenuUpdateStub())
            ->setKey($menuItem11Custom->getName())
            ->setCustom(true)
            ->setParentKey($menuItem11->getName())
            ->setMaxTraverseLevel(5);

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu_item' => $menuItem11Custom, 'menu' => $menu]
        );

        $this->assertFormOptionEqual([0, 1, 2, 3], 'choices', $form->get('maxTraverseLevel'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('maxTraverseLevel'));

        $contentNode = $this->createMock(ContentNode::class);
        $form->submit([
            'targetType' => MenuUpdate::TARGET_CONTENT_NODE,
            'contentNode' => $contentNode,
            'maxTraverseLevel' => 2,
        ]);

        $this->assertFormIsValid($form);

        $expected = (new MenuUpdateStub())
            ->setKey($menuUpdate->getKey())
            ->setCustom(true)
            ->setContentNode($contentNode)
            ->setParentKey($menuItem11->getName())
            ->setMaxTraverseLevel(2);

        self::assertEquals($expected, $form->getData());
    }

    public function testSubmitWhenMaxTraverseLevelIsNotRestrictedByParentBecauseSynthetic(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 6);

        $menuItem1 = $menu->addChild('sample_item_1');
        $menuItem11 = $menuItem1->addChild('sample_item_1_11')
            ->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 3);
        $menuItem11Synthetic = $menuItem11->addChild('sample_item_1_11_synthetic');

        $menuUpdate = (new MenuUpdateStub())
            ->setKey($menuItem11Synthetic->getName())
            ->setCustom(false)
            ->setSynthetic(true)
            ->setParentKey($menuItem11->getName())
            ->setMaxTraverseLevel(5);

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu_item' => $menuItem11Synthetic, 'menu' => $menu]
        );

        $this->assertFormOptionEqual([0, 1, 2, 3], 'choices', $form->get('maxTraverseLevel'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('maxTraverseLevel'));

        $form->submit([
            'maxTraverseLevel' => 2,
        ]);

        $this->assertFormIsValid($form);

        $expected = (new MenuUpdateStub())
            ->setKey($menuUpdate->getKey())
            ->setSynthetic(true)
            ->setParentKey($menuItem11->getName())
            ->setMaxTraverseLevel(2);

        self::assertEquals($expected, $form->getData());
    }

    public function testSubmitWhenMaxTraverseLevelIsRestrictedByParent(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 6);

        $menuItem1 = $menu->addChild('sample_item_1');
        $menuItem11 = $menuItem1->addChild('sample_item_1_11')
            ->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 2);
        $menuItem111 = $menuItem11->addChild('sample_item_1_111');

        $menuUpdate = (new MenuUpdateStub())
            ->setKey($menuItem111->getName())
            ->setCustom(false)
            ->setSynthetic(false)
            ->setParentKey($menuItem11->getName())
            ->setMaxTraverseLevel(5);

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu_item' => $menuItem111, 'menu' => $menu]
        );

        $this->assertFormOptionEqual([0, 1], 'choices', $form->get('maxTraverseLevel'));
        $this->assertFormOptionEqual(false, 'disabled', $form->get('maxTraverseLevel'));

        $form->submit([
            'maxTraverseLevel' => 1,
        ]);

        $this->assertFormIsValid($form);

        $expected = (new MenuUpdateStub())
            ->setKey($menuUpdate->getKey())
            ->setCustom(false)
            ->setSynthetic(false)
            ->setParentKey($menuItem11->getName())
            ->setMaxTraverseLevel(1);

        self::assertEquals($expected, $form->getData());
    }
}
