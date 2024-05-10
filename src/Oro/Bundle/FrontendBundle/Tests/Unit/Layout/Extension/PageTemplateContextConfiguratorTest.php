<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Layout\Extension;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Layout\Extension\PageTemplateContextConfigurator;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\ProductBundle\Provider\PageTemplateProvider;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use Oro\Component\Layout\LayoutContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageTemplateContextConfiguratorTest extends TestCase
{
    private ConfigManager|MockObject $configManager;

    private ThemeConfigurationProvider|MockObject $themeConfigurationProvider;

    private PageTemplateContextConfigurator $pageTemplateContextConfigurator;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->themeConfigurationProvider = $this->createMock(ThemeConfigurationProvider::class);

        $this->pageTemplateContextConfigurator = new PageTemplateContextConfigurator(
            $this->configManager,
            $this->themeConfigurationProvider
        );
    }

    public function testConfigureContextPageTemplateAlreadySet(): void
    {
        $context = new LayoutContext();
        $context->set('page_template', 'some_page_template');
        $this->pageTemplateContextConfigurator->configureContext($context);
        $context->resolve();
        self::assertSame('some_page_template', $context->get('page_template'));
    }

    public function testConfigureContextPageTemplateResolvedFromConfig(): void
    {
        $this->themeConfigurationProvider->expects(self::once())
            ->method('hasThemeConfigurationOption')
            ->with(ThemeConfiguration::buildOptionKey('product_details', 'template'))
            ->willReturn(false);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_frontend.page_templates')
            ->willReturn(['some_route' => 'some_page_template']);

        $context = new LayoutContext();
        $context->getResolver()->setDefault('route_name', 'some_route');
        $this->pageTemplateContextConfigurator->configureContext($context);
        $context->resolve();
        self::assertSame('some_page_template', $context->get('page_template'));
    }

    public function testConfigureContextPageTemplateNotAssigned(): void
    {
        $this->themeConfigurationProvider->expects(self::once())
            ->method('hasThemeConfigurationOption')
            ->with(ThemeConfiguration::buildOptionKey('product_details', 'template'))
            ->willReturn(false);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_frontend.page_templates')
            ->willReturn(['some_route' => 'some_page_template']);

        $context = new LayoutContext();
        $context->getResolver()->setDefault('route_name', 'some_other_route');
        $this->pageTemplateContextConfigurator->configureContext($context);
        $context->resolve();
        self::assertNull($context->get('page_template'));
    }

    public function testConfigureContextPageTemplateResolvedFromThemeConfiguration(): void
    {
        $this->themeConfigurationProvider->expects(self::once())
            ->method('hasThemeConfigurationOption')
            ->with(ThemeConfiguration::buildOptionKey('product_details', 'template'))
            ->willReturn(true);

        $this->themeConfigurationProvider->expects(self::once())
            ->method('getThemeConfigurationOption')
            ->with(ThemeConfiguration::buildOptionKey('product_details', 'template'))
            ->willReturn('theme_configuration_page_template');

        $this->configManager->expects(self::never())
            ->method('get')
            ->withAnyParameters();

        $context = new LayoutContext();
        $context->getResolver()->setDefault(
            'route_name',
            PageTemplateProvider::PRODUCT_DETAILS_PAGE_TEMPLATE_ROUTE_NAME
        );
        $this->pageTemplateContextConfigurator->configureContext($context);
        $context->resolve();
        self::assertSame('theme_configuration_page_template', $context->get('page_template'));
    }
}
