<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Configuration;

use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration as LayoutThemeConfiguration;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\ThemeBundle\Entity\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Form\Type\ThemeConfigurationType;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Form\FormFactoryInterface;

class CheckboxBuilderTest extends WebTestCase
{
    private FormFactoryInterface $formFactory;

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
            [LayoutThemeConfiguration::buildOptionKey('header', 'standalone_main_menu'), 'unchecked'],
        ];
    }

    /**
     * @dataProvider getShowSavedValueDataProvider
     */
    public function testShowSavedValue(string $optionKey, string|bool|null $savedValue): void
    {
        $themeConfiguration = (new ThemeConfiguration())
            ->addConfigurationOption($optionKey, $savedValue);
        ReflectionUtil::setPropertyValue($themeConfiguration, 'id', 1);

        $form = $this->formFactory->create(ThemeConfigurationType::class, $themeConfiguration);

        self::assertEquals($savedValue, $form->get('configuration')->get($optionKey)->getData());
    }

    public function getShowSavedValueDataProvider(): array
    {
        $key = LayoutThemeConfiguration::buildOptionKey('header', 'standalone_main_menu');

        return [
            [$key, 'unchecked'],
            [$key, false],
            [$key, 'checked'],
            [$key, true],
        ];
    }
}
