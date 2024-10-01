<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Configuration;

use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration as LayoutThemeConfiguration;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\ThemeBundle\Entity\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Form\Type\ThemeConfigurationType;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Form\FormFactoryInterface;

class SelectBuilderTest extends WebTestCase
{
    private FormFactoryInterface $formFactory;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();

        $this->formFactory = $this->getContainer()->get('form.factory');
    }

    /**
     * @dataProvider getPreSetDefaultValueDataProvider
     */
    public function testPreSetDefaultValue(string $optionKey, string $expectedDefaultValue): void
    {
        $form = $this->formFactory->create(ThemeConfigurationType::class, new ThemeConfiguration());

        self::assertEquals($expectedDefaultValue, $form->get('configuration')->get($optionKey)->getData());
    }

    public function getPreSetDefaultValueDataProvider(): array
    {
        return [
            [
                LayoutThemeConfiguration::buildOptionKey('header', 'language_and_currency_switchers'),
                'always_in_hamburger_menu'
            ],
            [LayoutThemeConfiguration::buildOptionKey('header', 'search_on_smaller_screens'), 'integrated'],
        ];
    }

    /**
     * @dataProvider getShowSavedValueDataProvider
     */
    public function testShowSavedValue(string $optionKey, string $savedValue): void
    {
        $themeConfiguration = (new ThemeConfiguration())
            ->addConfigurationOption($optionKey, $savedValue);
        ReflectionUtil::setPropertyValue($themeConfiguration, 'id', 1);

        $form = $this->formFactory->create(ThemeConfigurationType::class, $themeConfiguration);

        self::assertEquals($savedValue, $form->get('configuration')->get($optionKey)->getData());
    }

    public function getShowSavedValueDataProvider(): array
    {
        return [
            [LayoutThemeConfiguration::buildOptionKey('header', 'language_and_currency_switchers'), 'above_header'],
            [LayoutThemeConfiguration::buildOptionKey('header', 'search_on_smaller_screens'), 'standalone'],
        ];
    }
}
