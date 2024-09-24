<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Layout\Extension;

use Oro\Bundle\FrontendBundle\Layout\Extension\IconsThemeConfigurationExtension;
use Oro\Bundle\FrontendBundle\Tests\Unit\Fixtures\Bundle\TestBundle1\TestBundle1;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfigurationProvider;
use Oro\Bundle\ProductBundle\Form\Configuration\ProductPageTemplateBuilder;
use Oro\Bundle\ThemeBundle\Form\Configuration\CheckboxBuilder;
use Oro\Bundle\ThemeBundle\Form\Provider\ConfigurationBuildersProvider;
use Oro\Component\Config\CumulativeResourceManager;
use Oro\Component\Testing\TempDirExtension;
use PHPUnit\Framework\TestCase;

class IconsThemeConfigurationExtensionTest extends TestCase
{
    use TempDirExtension;

    private ThemeConfigurationProvider $themeConfigurationProvider;

    #[\Override]
    protected function setUp(): void
    {
        $cacheFile = $this->getTempFile('IconsThemeConfigurationExtension');

        $configurationProvider = $this->createMock(ConfigurationBuildersProvider::class);
        $configurationProvider->expects(self::once())
            ->method('getConfigurationTypes')
            ->willReturn([CheckboxBuilder::getType(), ProductPageTemplateBuilder::getType()]);

        $themeConfiguration = new ThemeConfiguration($configurationProvider);
        $themeConfiguration->addExtension(new IconsThemeConfigurationExtension());

        $this->themeConfigurationProvider = new ThemeConfigurationProvider(
            $cacheFile,
            false,
            $themeConfiguration,
            '[\w\-]+'
        );
    }

    public function testPrependIconsConfigs(): void
    {
        $bundle1 = new TestBundle1();
        CumulativeResourceManager::getInstance()
            ->clear()
            ->setBundles([$bundle1->getName() => get_class($bundle1)]);

        $themeDefinition = $this->themeConfigurationProvider->getThemeDefinition('sample_theme');

        self::assertEquals(
            [
                'fa_to_svg' => [
                    'fa-adjust' => 'adjust',
                ],
                'file_icons' => [
                    'default' => 'add-note',
                ],
            ],
            $themeDefinition['config']['icons']
        );
    }
}
