<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Extension;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Form\Type\CategoryTreeType;
use Oro\Bundle\CatalogBundle\JsTree\CategoryTreeHandler;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Form\DataTransformer\MenuUserAgentConditionsCollectionTransformer;
use Oro\Bundle\CommerceMenuBundle\Form\Extension\MenuUpdateExtension;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuScreensConditionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionsCollectionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionType;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub\ImageTypeStub;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub\MenuUpdateTypeStub;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Oro\Bundle\FormBundle\Autocomplete\SearchRegistry;
use Oro\Bundle\FormBundle\Form\Type\CollectionType as OroCollectionType;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\FormBundle\Form\Type\LinkTargetType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\TooltipFormExtensionStub;
use Oro\Bundle\FrontendBundle\Provider\ScreensProviderInterface;
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
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType as EntityTypeStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuUpdateExtensionTest extends FormIntegrationTestCase
{
    use MenuItemTestTrait;

    private const SCREENS_CONFIG = [
        'desktop' => [
            'label' => 'Sample desktop label',
            'hidingCssClass' => 'sample-desktop-class',
        ],
        'mobile' => [
            'label' => 'Sample mobile label',
            'hidingCssClass' => 'sample-mobile-class',
        ],
    ];

    private WebCatalogProvider|\PHPUnit\Framework\MockObject\MockObject $webCatalogProvider;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getExtensions(): array
    {
        $this->webCatalogProvider = $this->createMock(WebCatalogProvider::class);
        $menuTemplatesProvider = $this->createMock(MenuTemplatesProvider::class);
        $menuTemplatesProvider->expects(self::any())
            ->method('getMenuTemplates')
            ->willReturn([
                'list' => [
                    'label' => 'List',
                    'template' => 'list',
                ],
            ]);

        $screensProvider = $this->createMock(ScreensProviderInterface::class);
        $screensProvider->expects(self::any())
            ->method('getScreens')
            ->willReturn(self::SCREENS_CONFIG);

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
                    new CollectionType(),
                    new OroCollectionType(),
                    new MenuUserAgentConditionType(),
                    new MenuUserAgentConditionsCollectionType(new MenuUserAgentConditionsCollectionTransformer()),
                    new MenuScreensConditionType($screensProvider),
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
                    new LinkTargetType(),
                    ImageType::class => new ImageTypeStub(),
                    RouteChoiceType::class => new RouteChoiceTypeStub(['sample_route' => 'sample_route']),
                    new CategoryTreeType($this->createMock(CategoryTreeHandler::class)),
                    EntityType::class => new EntityTypeStub(),
                ],
                [
                    MenuUpdateTypeStub::class => [
                        new MenuUpdateExtension($this->webCatalogProvider, $menuTemplatesProvider),
                    ],
                    FormType::class => [new TooltipFormExtensionStub($this)],
                    ChoiceType::class => [
                        new TranslatableChoiceTypeExtension(),
                    ],
                ]
            ),
            $this->getValidatorExtension(true),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidators(): array
    {
        return [
            'oro_security.validator.constraints.not_dangerous_protocol' =>
                new NotDangerousProtocolValidator(new UriSecurityHelper([])),
        ];
    }

    public function testSubmitConditions(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menuUserAgentCondition = new MenuUserAgentCondition();
        $menuUserAgentCondition
            ->setOperation('contains')
            ->setValue('sample condition')
            ->setConditionGroupIdentifier(0);

        $menuUpdate = new MenuUpdateStub();
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 6);

        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate, ['menu' => $menu]);

        $image = new File();

        $form->submit(
            [
                'image' => $image,
                'condition' => 'false',
                'menuUserAgentConditions' => [
                    $menuUserAgentCondition->getConditionGroupIdentifier() => [
                        0 => [
                            'operation' => $menuUserAgentCondition->getOperation(),
                            'value' => $menuUserAgentCondition->getValue(),
                        ],
                    ],
                ],
                'screens' => $screens = ['desktop', 'mobile'],
                'linkTarget' => LinkTargetType::NEW_WINDOW_VALUE,
                'menuTemplate' => 'list',
            ]
        );

        $expected = (new MenuUpdateStub())
            ->setCondition('false')
            ->setImage($image)
            ->addMenuUserAgentCondition($menuUserAgentCondition)
            ->setScreens($screens)
            ->setLinkTarget(LinkTargetType::NEW_WINDOW_VALUE)
            ->setMenuTemplate('list');

        $this->assertFormIsValid($form);

        $disabledFieldNames = [
            'targetType',
            'webCatalog',
            'systemPageRoute',
            'contentNode',
            'category',
        ];
        foreach ($disabledFieldNames as $disabledFieldName) {
            $this->assertFormOptionEqual(true, 'disabled', $form->get($disabledFieldName));
        }
        $this->assertFormOptionEqual(false, 'disabled', $form->get('maxTraverseLevel'));
        self::assertEquals($expected, $form->getData());
    }

    public function testSubmitWhenMaxTraverseLevelIsDisabled(): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menuUpdate = new MenuUpdateStub();
        $menuUpdate->setMaxTraverseLevel(3);

        $menuItem = $this->createItem('sample_item');
        $menu = $this->createItem('sample_menu')
            ->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 1);
        $menu->addChild($menuItem);

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu_item' => $menuItem, 'menu' => $menu]
        );

        $form->submit([
            'linkTarget' => LinkTargetType::NEW_WINDOW_VALUE,
            'maxTraverseLevel' => 5,
        ]);

        $expected = (new MenuUpdateStub())
            ->setLinkTarget(0)
            ->setMaxTraverseLevel($menuUpdate->getMaxTraverseLevel());

        $this->assertFormIsValid($form);

        $this->assertFormOptionEqual(true, 'disabled', $form->get('maxTraverseLevel'));
        self::assertEquals($expected, $form->getData());
    }

    public function testSubmitMaxTraverseLevelWhenNotRestrictedByParentWhenNoMenuItem(): void
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

        $form->submit([
            'linkTarget' => LinkTargetType::NEW_WINDOW_VALUE,
            'maxTraverseLevel' => 2,
        ]);

        $this->assertFormIsValid($form);

        $this->assertFormOptionEqual(false, 'disabled', $form->get('maxTraverseLevel'));

        $expected = (new MenuUpdateStub())
            ->setKey($menuUpdate->getKey())
            ->setParentKey($menuItem11->getName())
            ->setLinkTarget(LinkTargetType::NEW_WINDOW_VALUE)
            ->setMaxTraverseLevel(2);
        self::assertEquals($expected, $form->getData());
    }

    public function testSubmitMaxTraverseLevelWhenNotRestrictedByParentWhenCustom(): void
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

        $contentNode = $this->createMock(ContentNode::class);
        $form->submit([
            'contentNode' => $contentNode,
            'linkTarget' => LinkTargetType::NEW_WINDOW_VALUE,
            'maxTraverseLevel' => 2,
        ]);

        $this->assertFormIsValid($form);

        $this->assertFormOptionEqual(false, 'disabled', $form->get('maxTraverseLevel'));

        $expected = (new MenuUpdateStub())
            ->setKey($menuUpdate->getKey())
            ->setCustom(true)
            ->setContentNode($contentNode)
            ->setParentKey($menuItem11->getName())
            ->setLinkTarget(LinkTargetType::NEW_WINDOW_VALUE)
            ->setMaxTraverseLevel(2);
        self::assertEquals($expected, $form->getData());
    }

    public function testSubmitMaxTraverseLevelWhenNotRestrictedByParentWhenSynthetic(): void
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

        $form->submit([
            'linkTarget' => LinkTargetType::NEW_WINDOW_VALUE,
            'maxTraverseLevel' => 2,
        ]);

        $this->assertFormIsValid($form);

        $this->assertFormOptionEqual(false, 'disabled', $form->get('maxTraverseLevel'));

        $expected = (new MenuUpdateStub())
            ->setKey($menuUpdate->getKey())
            ->setSynthetic(true)
            ->setParentKey($menuItem11->getName())
            ->setLinkTarget(LinkTargetType::NEW_WINDOW_VALUE)
            ->setMaxTraverseLevel(2);
        self::assertEquals($expected, $form->getData());
    }

    public function testSubmitMaxTraverseLevelWhenRestrictedByParent(): void
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

        $form->submit([
            'linkTarget' => LinkTargetType::NEW_WINDOW_VALUE,
            'maxTraverseLevel' => 1,
        ]);

        $this->assertFormIsValid($form);

        $this->assertFormOptionEqual(false, 'disabled', $form->get('maxTraverseLevel'));

        $expected = (new MenuUpdateStub())
            ->setKey($menuUpdate->getKey())
            ->setCustom(false)
            ->setSynthetic(false)
            ->setParentKey($menuItem11->getName())
            ->setLinkTarget(LinkTargetType::NEW_WINDOW_VALUE)
            ->setMaxTraverseLevel(1);
        self::assertEquals($expected, $form->getData());
    }

    /**
     * @dataProvider submitTargetPageDataProvider
     */
    public function testSubmitTargetPage(array $submitData, MenuUpdate $expectedMenuUpdate): void
    {
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn($this->createMock(WebCatalog::class));

        $menuUpdate = new MenuUpdateStub();
        $menuUpdate->setCustom(true);
        $menu = $this->createItem('sample_menu');

        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate, ['menu' => $menu]);

        $form->submit($submitData);

        $this->assertFormIsValid($form);
        self::assertEquals($expectedMenuUpdate, $form->getData());
    }

    public function submitTargetPageDataProvider(): array
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
                    'linkTarget' => LinkTargetType::SAME_WINDOW_VALUE,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setUri('sample/uri'),
            ],
            'system page target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_SYSTEM_PAGE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                    'linkTarget' => LinkTargetType::SAME_WINDOW_VALUE,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setSystemPageRoute('sample_route'),
            ],
            'content node target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CONTENT_NODE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                    'linkTarget' => LinkTargetType::SAME_WINDOW_VALUE,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setContentNode($contentNode),
            ],
            'category target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CATEGORY,
                    'uri' => 'sample/uri',
                    'category' => $category,
                    'contentNode' => $contentNode,
                    'linkTarget' => LinkTargetType::SAME_WINDOW_VALUE,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setCategory($category),
            ],
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
}
