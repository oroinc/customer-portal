<?php

namespace Oro\Bundle\CustomerMenuBundle\Tests\Unit\Form\Type;

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
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FormBundle\Form\Extension\TooltipFormExtension;
use Oro\Bundle\FormBundle\Form\Type\CollectionType as OroCollectionType;
use Oro\Bundle\FrontendBundle\Provider\ScreensProviderInterface;
use Oro\Bundle\NavigationBundle\Validator\Constraints\MaxNestedLevelValidator;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;

class MenuUpdateExtensionTest extends FormIntegrationTestCase
{
    /**
     * @internal
     */
    const SCREENS_CONFIG  = [
        'desktop' => [
            'label' => 'Sample desktop label',
            'hidingCssClass' => 'sample-desktop-class',
        ],
        'mobile' => [
            'label' => 'Sample mobile label',
            'hidingCssClass' => 'sample-mobile-class',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $configProvider = $this->createMock(ConfigProvider::class);
        $translator = $this->createMock(Translator::class);
        $screensProvider = $this->createMock(ScreensProviderInterface::class);
        $screensProvider
            ->expects(static::once())
            ->method('getScreens')
            ->willReturn(self::SCREENS_CONFIG);

        $transformer = new MenuUserAgentConditionsCollectionTransformer();

        return [
            new PreloadedExtension(
                [
                    new ImageTypeStub,
                    CollectionType::class => new CollectionType(),
                    OroCollectionType::class => new OroCollectionType(),
                    MenuUserAgentConditionType::class => new MenuUserAgentConditionType(),
                    MenuUserAgentConditionsCollectionType::class =>
                        new MenuUserAgentConditionsCollectionType($transformer),
                    MenuScreensConditionType::class => new MenuScreensConditionType($screensProvider),
                ],
                [
                    MenuUpdateTypeStub::class => [new MenuUpdateExtension()],
                    FormType::class => [new TooltipFormExtension($configProvider, $translator)]
                ]
            ),
            $this->getValidatorExtension(true)
        ];
    }

    public function testSubmitValid()
    {
        $menuUserAgentCondition = new MenuUserAgentCondition();
        $menuUserAgentCondition
            ->setOperation('contains')
            ->setValue('sample condition')
            ->setConditionGroupIdentifier(0);

        $screens = ['desktop', 'mobile'];

        $menuUpdate = new MenuUpdateStub();
        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate);

        $form->submit(
            [
                'uri' => 'localhost',
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
                'screens' => $screens,
            ]
        );

        $expected = new MenuUpdateStub();
        $expected->setUri('localhost');
        $expected->setCondition('false');
        // TODO fix it
        $expected->setImage('image.png');
        $expected->addMenuUserAgentCondition($menuUserAgentCondition);
        $expected->setScreens($screens);

        $this->assertFormIsValid($form);
        $this->assertEquals($expected, $form->getData());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ConstraintValidatorFactoryInterface
     */
    protected function getConstraintValidatorFactory()
    {
        /* @var $factory \PHPUnit_Framework_MockObject_MockObject|ConstraintValidatorFactoryInterface */
        $factory = $this->createMock('Symfony\Component\Validator\ConstraintValidatorFactoryInterface');

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

                    if (!isset($this->validators[$className]) ||
                        $className === 'Symfony\Component\Validator\Constraints\CollectionValidator'
                    ) {
                        $this->validators[$className] = new $className();
                    }

                    return $this->validators[$className];
                }
            );

        return $factory;
    }
}
