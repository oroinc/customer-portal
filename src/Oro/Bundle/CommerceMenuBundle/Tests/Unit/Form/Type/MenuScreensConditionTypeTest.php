<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuScreensConditionType;
use Oro\Bundle\FrontendBundle\Provider\ScreensProviderInterface;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuScreensConditionTypeTest extends FormIntegrationTestCase
{
    /**
     * @internal
     */
    const SCREENS_CONFIG = [
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
     * @internal
     */
    const SCREENS_CHOICES = [
        'Sample desktop label' => 'desktop',
        'Sample mobile label' => 'mobile',
    ];

    /**
     * @var MenuScreensConditionType
     */
    private $formType;

    /**
     * @var ScreensProviderInterface
     */
    private $screensProvider;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->screensProvider = $this->createMock(ScreensProviderInterface::class);
        $this->formType = new MenuScreensConditionType($this->screensProvider);

        parent::setUp();
    }


    /**
     * {@inheritDoc}
     */
    protected function getExtensions()
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
        $this->mockScreensProvider();

        $selectedScreens = ['desktop', 'mobile'];
        $form = $this->factory->create(MenuScreensConditionType::class, []);
        $form->submit($selectedScreens);

        $this->assertFormIsValid($form);
        $this->assertEquals($selectedScreens, $form->getData());
    }

    public function testGetBlockPrefix()
    {
        static::assertEquals('oro_commerce_menu_screens_condition', $this->formType->getBlockPrefix());
    }

    public function testConfigureOptions()
    {
        $this->mockScreensProvider();

        $optionsResolver = new OptionsResolver();
        $this->formType->configureOptions($optionsResolver);

        $actualOptions = $optionsResolver->resolve([]);
        $expectedOptions = [
            'choices' => self::SCREENS_CHOICES,
            'multiple' => true,
            'required' => false,
        ];

        static::assertEquals($expectedOptions, $actualOptions);
    }

    private function mockScreensProvider()
    {
        $this->screensProvider
            ->expects(static::once())
            ->method('getScreens')
            ->willReturn(self::SCREENS_CONFIG);
    }
}
