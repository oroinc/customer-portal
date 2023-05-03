<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Layout\Extension;

use Oro\Bundle\CommerceMenuBundle\Layout\Extension\MenuTemplatesThemeConfigurationExtension;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Fixtures\Bundle\FooBundle\FooBundle;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfigurationProvider;
use Oro\Component\Config\CumulativeResourceManager;
use Oro\Component\Testing\TempDirExtension;

class MenuTemplatesThemeConfigurationExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TempDirExtension;

    private ThemeConfigurationProvider $themeConfigurationProvider;

    protected function setUp(): void
    {
        $cacheFile = $this->getTempFile('MenuTemplatesThemeConfigurationExtension');

        $themeConfiguration = new ThemeConfiguration();
        $themeConfiguration->addExtension(new MenuTemplatesThemeConfigurationExtension());

        $this->themeConfigurationProvider = new ThemeConfigurationProvider(
            $cacheFile,
            false,
            $themeConfiguration,
            '[\w\-]+'
        );
    }

    public function testPrependMenuTemplatesConfigs(): void
    {
        $fooBundle = new FooBundle();
        CumulativeResourceManager::getInstance()
            ->clear()
            ->setBundles([$fooBundle->getName() => get_class($fooBundle)]);

        $themeDefinition = $this->themeConfigurationProvider->getThemeDefinition('foo_bundle_theme');

        self::assertEquals(
            [
                'foo_menu_template' => [
                    'label' => 'Foo menu',
                    'template' => 'foo_menu_template',
                ],
                'bar_menu_template' => [
                    'label' => 'Bar menu',
                    'template' => 'bar_menu_template',
                ],
            ],
            $themeDefinition['config']['menu_templates']
        );
    }
}
