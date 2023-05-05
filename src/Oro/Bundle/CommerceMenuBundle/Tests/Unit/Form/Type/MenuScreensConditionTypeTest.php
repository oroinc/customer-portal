<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuScreensConditionType;
use Oro\Bundle\FrontendBundle\Provider\ScreensProviderInterface;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuScreensConditionTypeTest extends FormIntegrationTestCase
{
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
    private const SCREENS_CHOICES = [
        'Sample desktop label' => 'desktop',
        'Sample mobile label' => 'mobile',
    ];

    /** @var ScreensProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $screensProvider;

    /** @var MenuScreensConditionType */
    private $formType;

    protected function setUp(): void
    {
        $this->screensProvider = $this->createMock(ScreensProviderInterface::class);

        $this->formType = new MenuScreensConditionType($this->screensProvider);

        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    MenuScreensConditionType::class => new MenuScreensConditionType($this->screensProvider),
                ],
                []
            )
        ];
    }

    public function testSubmitValid()
    {
        $this->screensProvider->expects(self::once())
            ->method('getScreens')
            ->willReturn(self::SCREENS_CONFIG);

        $selectedScreens = ['desktop', 'mobile'];
        $form = $this->factory->create(MenuScreensConditionType::class, []);
        $form->submit($selectedScreens);

        $this->assertFormIsValid($form);
        $this->assertEquals($selectedScreens, $form->getData());
    }

    public function testGetBlockPrefix()
    {
        self::assertEquals('oro_commerce_menu_screens_condition', $this->formType->getBlockPrefix());
    }

    public function testConfigureOptions()
    {
        $this->screensProvider->expects(self::once())
            ->method('getScreens')
            ->willReturn(self::SCREENS_CONFIG);

        $optionsResolver = new OptionsResolver();
        $this->formType->configureOptions($optionsResolver);

        $actualOptions = $optionsResolver->resolve([]);
        $expectedOptions = [
            'choices' => self::SCREENS_CHOICES,
            'multiple' => true,
            'required' => false,
        ];

        self::assertEquals($expectedOptions, $actualOptions);
    }
}
