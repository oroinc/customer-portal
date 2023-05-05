<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Extension;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Form\DataTransformer\MenuUserAgentConditionsCollectionTransformer;
use Oro\Bundle\CommerceMenuBundle\Form\Extension\MenuUpdateConditionsExtension;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuScreensConditionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionsCollectionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionType;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub\MenuUpdateTypeStub;
use Oro\Bundle\FormBundle\Form\Type\CollectionType as OroCollectionType;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\TooltipFormExtensionStub;
use Oro\Bundle\FrontendBundle\Provider\ScreensProviderInterface;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\SecurityBundle\Util\UriSecurityHelper;
use Oro\Bundle\SecurityBundle\Validator\Constraints\NotDangerousProtocolValidator;
use Oro\Bundle\TranslationBundle\Form\Extension\TranslatableChoiceTypeExtension;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class MenuUpdateConditionsExtensionTest extends FormIntegrationTestCase
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

    protected function getExtensions(): array
    {
        $screensProvider = $this->createMock(ScreensProviderInterface::class);
        $screensProvider->expects(self::any())
            ->method('getScreens')
            ->willReturn(self::SCREENS_CONFIG);

        return [
            new PreloadedExtension(
                [
                    new CollectionType(),
                    new OroCollectionType(),
                    new MenuUserAgentConditionType(),
                    new MenuUserAgentConditionsCollectionType(new MenuUserAgentConditionsCollectionTransformer()),
                    new MenuScreensConditionType($screensProvider),
                ],
                [
                    MenuUpdateTypeStub::class => [new MenuUpdateConditionsExtension()],
                    FormType::class => [new TooltipFormExtensionStub($this)],
                    ChoiceType::class => [
                        new TranslatableChoiceTypeExtension(),
                    ],
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

    public function testFormHasConditionField(): void
    {
        $menu = $this->createItem('sample_menu');
        $existingCondition = 'existingCondition()';
        $menuUpdate = (new MenuUpdateStub())
            ->setCondition($existingCondition);

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        self::assertTrue($form->has('condition'));
        self::assertEquals($existingCondition, $form->get('condition')->getData());
    }

    public function testSubmitConditionField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = new MenuUpdateStub();

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        $condition = 'sampleCondition()';

        $form->submit(['condition' => $condition]);

        self::assertEquals($condition, $form->getData()->getCondition());
    }

    public function testFormHasMenuUserAgentConditionsField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = new MenuUpdateStub();
        $existingMenuUserAgentCondition = (new MenuUserAgentCondition())
            ->setMenuUpdate($menuUpdate)
            ->setOperation('contains')
            ->setValue('sample condition')
            ->setConditionGroupIdentifier(0);
        $menuUpdate->addMenuUserAgentCondition($existingMenuUserAgentCondition);

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        self::assertTrue($form->has('menuUserAgentConditions'));
        self::assertEquals([[$existingMenuUserAgentCondition]], $form->get('menuUserAgentConditions')->getData());
    }

    public function testSubmitMenuUserAgentConditionsField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = new MenuUpdateStub();

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        $menuUserAgentCondition = new MenuUserAgentCondition();
        $menuUserAgentCondition
            ->setMenuUpdate($menuUpdate)
            ->setOperation('contains')
            ->setValue('sample condition')
            ->setConditionGroupIdentifier(0);

        $form->submit([
            'menuUserAgentConditions' => [
                $menuUserAgentCondition->getConditionGroupIdentifier() => [
                    0 => [
                        'operation' => $menuUserAgentCondition->getOperation(),
                        'value' => $menuUserAgentCondition->getValue(),
                    ],
                ],
            ],
        ]);

        self::assertEquals(
            new ArrayCollection([$menuUserAgentCondition]),
            $form->getData()->getMenuUserAgentConditions()
        );
    }

    public function testFormHasScreensField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = (new MenuUpdateStub())
            ->setScreens(['mobile']);

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        self::assertTrue($form->has('screens'));
        self::assertEquals(['mobile'], $form->get('screens')->getData());

        $this->assertFormOptionEqual(
            [
                'Sample desktop label' => 'desktop',
                'Sample mobile label' => 'mobile',
            ],
            'choices',
            $form->get('screens')
        );
    }

    public function testSubmitScreensField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = new MenuUpdateStub();

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        $screens = ['desktop', 'mobile'];

        $form->submit(['screens' => $screens]);

        self::assertEquals($screens, $form->getData()->getScreens());
    }
}
