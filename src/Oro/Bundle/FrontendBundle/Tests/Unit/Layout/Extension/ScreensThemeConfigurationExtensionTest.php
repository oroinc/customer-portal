<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Layout\Extension;

use Oro\Bundle\FrontendBundle\Layout\Extension\ScreensThemeConfigurationExtension;
use Oro\Bundle\FrontendBundle\Tests\Unit\Fixtures\Bundle\TestBundle1\TestBundle1;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfigurationProvider;
use Oro\Bundle\ThemeBundle\Form\Provider\ConfigurationBuildersProvider;
use Oro\Component\Config\CumulativeResourceManager;
use Oro\Component\Testing\TempDirExtension;
use PHPUnit\Framework\TestCase;

class ScreensThemeConfigurationExtensionTest extends TestCase
{
    use TempDirExtension;

    private ThemeConfigurationProvider $themeConfigurationProvider;

    protected function setUp(): void
    {
        $cacheFile = $this->getTempFile('ScreensThemeConfigurationExtension');

        $configurationProvider = $this->createMock(ConfigurationBuildersProvider::class);
        $configurationProvider->expects(self::once())
            ->method('getConfigurationTypes')
            ->willReturn(['type']);

        $themeConfiguration = new ThemeConfiguration($configurationProvider);
        $themeConfiguration->addExtension(new ScreensThemeConfigurationExtension());

        $this->themeConfigurationProvider = new ThemeConfigurationProvider(
            $cacheFile,
            false,
            $themeConfiguration,
            '[\w\-]+'
        );
    }

    public function testPrependScreensConfigs()
    {
        $bundle1 = new TestBundle1();
        CumulativeResourceManager::getInstance()
            ->clear()
            ->setBundles([$bundle1->getName() => get_class($bundle1)]);

        $themeDefinition = $this->themeConfigurationProvider->getThemeDefinition('sample_theme');

        $this->assertEquals(
            [
                'sample_screen' => [
                    'label'          => 'Sample screen',
                    'hidingCssClass' => 'sample-css-class'
                ]
            ],
            $themeDefinition['config']['screens']
        );
    }
}
