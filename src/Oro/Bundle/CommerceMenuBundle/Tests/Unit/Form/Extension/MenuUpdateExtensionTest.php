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

        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate);

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
                'linkTarget' => 0,
                'menuTemplate' => 'list',
            ]
        );

        $expected = (new MenuUpdateStub())
            ->setCondition('false')
            ->setImage($image)
            ->addMenuUserAgentCondition($menuUserAgentCondition)
            ->setScreens($screens)
            ->setLinkTarget(0)
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
            self::assertTrue($form->get($disabledFieldName)->getConfig()->getOption('disabled'));
        }
        self::assertFalse($form->get('maxTraverseLevel')->getConfig()->getOption('disabled'));
        self::assertEquals($expected, $form->getData());
    }

    public function testSubmitWhenMaxTraverseLevelIsDisabled(): void
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
        $menuUpdate->setMaxTraverseLevel(3);

        $menuItem = $this->createItem('sample_item')
            ->setExtra('max_traverse_level_disabled', true);
        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate, ['menu_item' => $menuItem]);

        $form->submit([
            'linkTarget' => 0,
            'maxTraverseLevel' => 5,
        ]);

        $expected = (new MenuUpdateStub())
            ->setLinkTarget(0)
            ->setMaxTraverseLevel($menuUpdate->getMaxTraverseLevel());

        $this->assertFormIsValid($form);

        self::assertTrue($form->get('maxTraverseLevel')->getConfig()->getOption('disabled'));
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

        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate);

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
                    'linkTarget' => 1,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setUri('sample/uri'),
            ],
            'system page target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_SYSTEM_PAGE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                    'linkTarget' => 1,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setSystemPageRoute('sample_route'),
            ],
            'content node target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CONTENT_NODE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                    'linkTarget' => 1,
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setContentNode($contentNode),
            ],
            'category target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CATEGORY,
                    'uri' => 'sample/uri',
                    'category' => $category,
                    'contentNode' => $contentNode,
                    'linkTarget' => 1,
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
