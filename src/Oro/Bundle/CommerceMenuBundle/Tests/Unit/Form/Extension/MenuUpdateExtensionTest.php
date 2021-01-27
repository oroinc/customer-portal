<?php

namespace Oro\Bundle\CustomerMenuBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Form\DataTransformer\MenuUserAgentConditionsCollectionTransformer;
use Oro\Bundle\CommerceMenuBundle\Form\Extension\MenuUpdateExtension;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuScreensConditionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionsCollectionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionType;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub\ImageTypeStub;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub\MenuUpdateTypeStub;
use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\MenuUpdateExpressionValidator;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Oro\Bundle\FormBundle\Autocomplete\SearchRegistry;
use Oro\Bundle\FormBundle\Form\Extension\TooltipFormExtension;
use Oro\Bundle\FormBundle\Form\Type\CollectionType as OroCollectionType;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\FormBundle\Form\Type\LinkTargetType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Oro\Bundle\FrontendBundle\Provider\ScreensProviderInterface;
use Oro\Bundle\NavigationBundle\Form\Type\RouteChoiceType;
use Oro\Bundle\NavigationBundle\Tests\Unit\Form\Type\Stub\RouteChoiceTypeStub;
use Oro\Bundle\NavigationBundle\Validator\Constraints\MaxNestedLevelValidator;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Form\Type\ContentNodeFromWebCatalogSelectType;
use Oro\Bundle\WebCatalogBundle\JsTree\ContentNodeTreeHandler;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;

class MenuUpdateExtensionTest extends FormIntegrationTestCase
{
    /**
     * @internal
     */
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

    /** @var WebCatalogProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $webCatalogProvider;

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $this->webCatalogProvider = $this->createMock(WebCatalogProvider::class);

        $screensProvider = $this->createMock(ScreensProviderInterface::class);
        $screensProvider
            ->expects($this->any())
            ->method('getScreens')
            ->willReturn(self::SCREENS_CONFIG);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry
            ->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager = $this->createMock(EntityManager::class));

        $entityManager
            ->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($classMetadata = new ClassMetadata(WebCatalog::class));

        $classMetadata->setIdentifier(['id']);

        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($repo = $this->createMock(EntityRepository::class));

        $repo
            ->expects($this->any())
            ->method('find')
            ->willReturn($this->createMock(ContentNode::class));

        $handler = $this->createMock(SearchHandlerInterface::class);
        $handler
            ->expects($this->any())
            ->method('getProperties')
            ->willReturn([]);

        $handler
            ->expects($this->any())
            ->method('getEntityName')
            ->willReturn(Product::class);

        $searchRegistry = $this->createMock(SearchRegistry::class);
        $searchRegistry
            ->expects($this->any())
            ->method('getSearchHandler')
            ->willReturn($handler);

        $configManager = $this->createMock(ConfigManager::class);

        $configManager
            ->expects($this->any())
            ->method('getProvider')
            ->willReturn($configProvider = $this->mockConfigProvider());

        return [
            new PreloadedExtension(
                [
                    ImageType::class => new ImageTypeStub,
                    CollectionType::class => new CollectionType(),
                    OroCollectionType::class => new OroCollectionType(),
                    MenuUserAgentConditionType::class => new MenuUserAgentConditionType(),
                    MenuUserAgentConditionsCollectionType::class =>
                        new MenuUserAgentConditionsCollectionType(new MenuUserAgentConditionsCollectionTransformer()),
                    MenuScreensConditionType::class => new MenuScreensConditionType($screensProvider),
                    OroEntitySelectOrCreateInlineType::class => new OroEntitySelectOrCreateInlineType(
                        $this->createMock(AuthorizationCheckerInterface::class),
                        $configManager,
                        $entityManager,
                        $searchRegistry
                    ),
                    OroJquerySelect2HiddenType::class => new OroJquerySelect2HiddenType(
                        $entityManager,
                        $searchRegistry,
                        $configProvider
                    ),
                    ContentNodeFromWebCatalogSelectType::class =>
                        new ContentNodeFromWebCatalogSelectType($this->createMock(ContentNodeTreeHandler::class)),
                    EntityIdentifierType::class => new EntityIdentifierType($managerRegistry),
                    RouteChoiceType::class => new RouteChoiceTypeStub(['sample_route' => 'sample_route']),
                    LinkTargetType::class => new LinkTargetType()
                ],
                [
                    MenuUpdateTypeStub::class => [new MenuUpdateExtension($this->webCatalogProvider)],
                    FormType::class =>
                        [new TooltipFormExtension($configProvider, $this->createMock(Translator::class))],
                ]
            ),
            $this->getValidatorExtension(true)
        ];
    }

    public function testSubmitConditions(): void
    {
        $this->webCatalogProvider
            ->expects($this->never())
            ->method('getWebCatalog');

        $menuUserAgentCondition = new MenuUserAgentCondition();
        $menuUserAgentCondition
            ->setOperation('contains')
            ->setValue('sample condition')
            ->setConditionGroupIdentifier(0);

        $menuUpdate = new MenuUpdateStub();

        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate);

        $form->submit(
            [
                'image' => 'image.png',
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
                'linkTarget' => 0
            ]
        );

        $expected = new MenuUpdateStub();
        $expected->setCondition('false');
        $expected->setImage('image.png');
        $expected->addMenuUserAgentCondition($menuUserAgentCondition);
        $expected->setScreens($screens);
        $expected->setLinkTarget(0);

        $this->assertFormIsValid($form);
        $this->assertEquals($expected, $form->getData());
    }

    /**
     * @dataProvider submitTargetPageDataProvider
     *
     * @param array $submitData
     * @param MenuUpdate $expectedMenuUpdate
     */
    public function testSubmitTargetPage(array $submitData, MenuUpdate $expectedMenuUpdate): void
    {
        $this->webCatalogProvider
            ->expects($this->once())
            ->method('getWebCatalog')
            ->willReturn($webCatalog = $this->createMock(WebCatalog::class));

        $menuUpdate = new MenuUpdateStub();
        $menuUpdate->setCustom(true);

        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate);

        $form->submit($submitData);

        $this->assertFormIsValid($form);
        $this->assertEquals($expectedMenuUpdate, $form->getData());
    }

    /**
     * @return array
     */
    public function submitTargetPageDataProvider(): array
    {
        $contentNode = $this->createMock(ContentNode::class);

        return [
            'uri target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_URI,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                    'linkTarget' => 1
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setUri('sample/uri'),
            ],
            'system page target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_SYSTEM_PAGE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                    'linkTarget' => 1
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setSystemPageRoute('sample_route'),
            ],
            'content node target type' => [
                'submitData' => [
                    'targetType' => MenuUpdate::TARGET_CONTENT_NODE,
                    'uri' => 'sample/uri',
                    'systemPageRoute' => 'sample_route',
                    'contentNode' => $contentNode,
                    'linkTarget' => 1
                ],
                'expectedMenuUpdate' => (new MenuUpdateStub())->setCustom(true)->setContentNode($contentNode),
            ],
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ConstraintValidatorFactoryInterface
     */
    protected function getConstraintValidatorFactory()
    {
        $factory = $this->createMock(ConstraintValidatorFactoryInterface::class);

        $mockedValidators = [MaxNestedLevelValidator::class, MenuUpdateExpressionValidator::class];

        $factory->expects($this->any())
            ->method('getInstance')
            ->willReturnCallback(
                function (Constraint $constraint) use ($mockedValidators) {
                    $className = $constraint->validatedBy();

                    foreach ($mockedValidators as $mockedValidator) {
                        $this->validators[$className] = $this->getMockBuilder($mockedValidator)
                            ->disableOriginalConstructor()
                            ->getMock();
                    }

                    if (!isset($this->validators[$className]) || $className === CollectionValidator::class) {
                        $this->validators[$className] = new $className();
                    }

                    return $this->validators[$className];
                }
            );

        return $factory;
    }

    /**
     * @return ConfigProvider
     */
    private function mockConfigProvider(): ConfigProvider
    {
        $configProvider = $this->createMock(ConfigProvider::class);

        $configProvider
            ->expects($this->any())
            ->method('getConfig')
            ->willReturn($config = $this->createMock(Config::class));

        $config
            ->expects($this->any())
            ->method('has')
            ->with('grid_name')
            ->willReturn(true);

        $config
            ->expects($this->any())
            ->method('get')
            ->with('grid_name')
            ->willReturn('sample-grid');

        return $configProvider;
    }
}
