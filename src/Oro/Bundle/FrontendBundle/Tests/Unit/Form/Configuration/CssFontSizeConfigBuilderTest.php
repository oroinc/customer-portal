<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Configuration;

use Oro\Bundle\FrontendBundle\Form\Configuration\CssFontSizeConfigBuilder;
use Symfony\Component\Asset\Packages;

final class CssFontSizeConfigBuilderTest extends AbstractCssConfigBuilderTest
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $packages = $this->createMock(Packages::class);
        $this->configBuilder = new CssFontSizeConfigBuilder($packages, $this->translator);
    }

    public function testThatTypeConfigured(): void
    {
        self::assertEquals('css_font_size', $this->configBuilder::getType());
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

    #[\Override]
    protected function getSupportsDataProvider(): array
    {
        return [
            ['unknown_type', false],
            [CssFontSizeConfigBuilder::getType(), true],
        ];
    }

    #[\Override]
    protected function getValidValueDataProvider(): array
    {
        return [
            ['16px'],
            ['1.5em'],
            ['120%'],
            ['large'],
        ];
    }
}