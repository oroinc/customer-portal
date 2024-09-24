<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Configuration;

use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration as LayoutThemeConfiguration;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\ThemeBundle\Entity\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Form\Type\ThemeConfigurationType;
use Symfony\Component\Form\FormFactoryInterface;

class QuickAccessButtonConfigBuilderTest extends WebTestCase
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
    public function testPreSetDefaultValue(string $optionKey, string|null $expectedDefaultValue): void
    {
        $form = $this->formFactory->create(ThemeConfigurationType::class, new ThemeConfiguration());

        self::assertEquals($expectedDefaultValue, $form->get('configuration')->get($optionKey)->getData());
    }

    public function getPreSetDefaultValueDataProvider(): array
    {
        return [
            [LayoutThemeConfiguration::buildOptionKey('header', 'quick_access_button'), null],
        ];
    }

    public function testShowSavedValue(): void
    {
        $optionKey = LayoutThemeConfiguration::buildOptionKey('header', 'quick_access_button');
        $savedValue = (new QuickAccessButtonConfig())
            ->setLabel(['' => 'label'])
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('frontend_menu');
        $themeConfiguration = (new ThemeConfiguration())
            ->addConfigurationOption($optionKey, $savedValue);

        $form = $this->formFactory->create(ThemeConfigurationType::class, $themeConfiguration);

        self::assertEquals($savedValue, $form->get('configuration')->get($optionKey)->getData());
    }
}
