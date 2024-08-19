<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Configuration;

use Oro\Bundle\FrontendBundle\Form\Configuration\CssLengthConfigBuilder;
use Symfony\Component\Asset\Packages;

final class CssLengthConfigBuilderTest extends AbstractCssConfigBuilderTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $packages = $this->createMock(Packages::class);
        $this->configBuilder = new CssLengthConfigBuilder($packages, $this->translator);
    }

    public function testThatTypeConfigured(): void
    {
        self::assertEquals('css_length', $this->configBuilder::getType());
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
            [CssLengthConfigBuilder::getType(), true],
        ];
    }

    protected function getValidValueDataProvider(): array
    {
        return [
            ['10px'],
            ['2em'],
            ['5%'],
            ['auto']
        ];
    }
}
