<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Configuration;

use Oro\Bundle\FrontendBundle\Form\Configuration\CssBackgroundConfigBuilder;
use Symfony\Component\Asset\Packages;

final class CssBackgroundConfigBuilderTest extends AbstractCssConfigBuilderTest
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $packages = $this->createMock(Packages::class);
        $this->configBuilder = new CssBackgroundConfigBuilder($packages, $this->translator);
    }

    public function testThatTypeConfigured(): void
    {
        self::assertEquals('css_background', $this->configBuilder::getType());
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
            [CssBackgroundConfigBuilder::getType(), true],
        ];
    }

    #[\Override]
    protected function getValidValueDataProvider(): array
    {
        return [
            ['#fff',],
            ['none'],
            ['red'],
        ];
    }
}
