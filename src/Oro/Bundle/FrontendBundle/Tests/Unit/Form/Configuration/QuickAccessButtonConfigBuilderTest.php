<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Configuration;

use Oro\Bundle\FrontendBundle\Form\Configuration\QuickAccessButtonConfigBuilder;
use Oro\Bundle\FrontendBundle\Form\Type\QuickAccessButtonConfigType;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

final class QuickAccessButtonConfigBuilderTest extends TestCase
{
    private FormBuilderInterface $formBuilder;

    private QuickAccessButtonConfigBuilder $quickAccessButtonConfigBuilder;

    protected function setUp(): void
    {
        $this->formBuilder = $this->createMock(FormBuilderInterface::class);
        $this->quickAccessButtonConfigBuilder = new QuickAccessButtonConfigBuilder();
    }

    public function testThatOptionSelectSupported(): void
    {
        self::assertTrue($this->quickAccessButtonConfigBuilder->supports(['type'=> 'quick_access_button_config']));
    }

    /**
     * @dataProvider optionDataProvider
     */
    public function testThatOptionBuiltCorrectly(array $option, array $expected): void
    {
        $this->formBuilder
            ->expects(self::once())
            ->method('add')
            ->with(
                $expected['name'],
                $expected['form_type'],
                $expected['options']
            );

        $this->quickAccessButtonConfigBuilder->buildOption($this->formBuilder, $option);
    }

    private function optionDataProvider(): array
    {
        return [
            'no previews' => [
                [
                    'name' => 'general-quick-access-button',
                    'label' => 'Select',
                    'type' => 'quick_access_button_config',
                    'default' => null
                ],
                [
                    'name' => 'general-quick-access-button',
                    'form_type' => QuickAccessButtonConfigType::class,
                    'options' => [
                        'label' => 'Select',
                        'attr' => [],
                        'by_reference' => false,
                        'empty_data' => new QuickAccessButtonConfig()
                    ]
                ]
            ]
        ];
    }
}
