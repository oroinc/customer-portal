<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Layout\Extension;

use Oro\Bundle\FrontendBundle\Layout\Extension\PageTemplatesThemeConfigurationExtension;
use Oro\Bundle\FrontendBundle\Tests\Unit\Fixtures\Bundle\TestBundle1\TestBundle1;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfigurationProvider;
use Oro\Bundle\ProductBundle\Form\Configuration\ProductPageTemplateBuilder;
use Oro\Bundle\ProductBundle\Provider\PageTemplateProvider;
use Oro\Bundle\ThemeBundle\Form\Configuration\CheckboxBuilder;
use Oro\Bundle\ThemeBundle\Form\Provider\ConfigurationBuildersProvider;
use Oro\Component\Config\CumulativeResourceManager;
use Oro\Component\Testing\TempDirExtension;
use PHPUnit\Framework\TestCase;

class PageTemplatesThemeConfigurationExtensionTest extends TestCase
{
    use TempDirExtension;

    private ThemeConfigurationProvider $themeConfigurationProvider;

    #[\Override]
    protected function setUp(): void
    {
        $cacheFile = $this->getTempFile('PageTemplatesThemeConfigurationExtension');

        $configurationProvider = $this->createMock(ConfigurationBuildersProvider::class);
        $configurationProvider->expects(self::once())
            ->method('getConfigurationTypes')
            ->willReturn([CheckboxBuilder::getType(), ProductPageTemplateBuilder::getType()]);

        $themeConfiguration = new ThemeConfiguration($configurationProvider);
        $themeConfiguration->addExtension(new PageTemplatesThemeConfigurationExtension());

        $this->themeConfigurationProvider = new ThemeConfigurationProvider(
            $cacheFile,
            false,
            $themeConfiguration,
            '[\w\-]+'
        );
    }

    public function testPrependScreensConfigs(): void
    {
        $bundle1 = new TestBundle1();
        CumulativeResourceManager::getInstance()
            ->clear()
            ->setBundles([$bundle1->getName() => get_class($bundle1)]);

        $themeDefinition = $this->themeConfigurationProvider->getThemeDefinition('sample_theme');

        $this->assertEquals(
            [
                'titles' => ['page_templates_title_key' => 'page_templates_title'],
                'templates' => [
                    [
                        'key' => 'tabs',
                        'label' => 'tabs',
                        'route_name' => PageTemplateProvider::PRODUCT_DETAILS_PAGE_TEMPLATE_ROUTE_NAME
                    ], [
                        'key' => 'wide',
                        'label' => 'wide',
                        'route_name' => PageTemplateProvider::PRODUCT_DETAILS_PAGE_TEMPLATE_ROUTE_NAME
                    ], [
                        'key' => 'mobile',
                        'label' => 'mobile',
                        'route_name' => PageTemplateProvider::PRODUCT_DETAILS_PAGE_TEMPLATE_ROUTE_NAME
                    ],
                ]
            ],
            $themeDefinition['config']['page_templates']
        );
    }
}
