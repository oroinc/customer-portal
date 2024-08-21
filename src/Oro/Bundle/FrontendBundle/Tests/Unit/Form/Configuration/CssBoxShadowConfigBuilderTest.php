<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Configuration;

use Oro\Bundle\FrontendBundle\Form\Configuration\CssBoxShadowConfigBuilder;
use Symfony\Component\Asset\Packages;

final class CssBoxShadowConfigBuilderTest extends AbstractCssConfigBuilderTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $packages = $this->createMock(Packages::class);
        $this->configBuilder = new CssBoxShadowConfigBuilder($packages, $this->translator);
    }

    public function testThatTypeConfigured(): void
    {
        self::assertEquals('css_box_shadow', $this->configBuilder::getType());
    }

    public function testThatFormAllowsOverrideRegexPattern(): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->setRegexPattern('/^[#a-zA-Z\d ]+$/');

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'section__option',
                'label' => 'label'
            ]
        );

        $form = $formBuilder->getForm();

        $form->submit(['section__option' => ['value' => '#00 0000']]);
        self::assertTrue($form->isValid());
    }

    protected function getSupportsDataProvider(): array
    {
        return [
            ['unknown_type', false],
            [CssBoxShadowConfigBuilder::getType(), true],
        ];
    }

    protected function getValidValueDataProvider(): array
    {
        return [
            ['10px 5px'],
            ['10px 5px 5px'],
            ['10px 5px 5px red'],
            ['inset 10px 5px 5px black'],
        ];
    }
}
