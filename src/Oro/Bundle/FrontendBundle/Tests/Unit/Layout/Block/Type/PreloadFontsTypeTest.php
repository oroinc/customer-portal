<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Layout\Block\Type;

use Oro\Bundle\FrontendBundle\Layout\Block\Type\PreloadFontsType;
use Oro\Bundle\LayoutBundle\Layout\Block\Type\ConfigurableType;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use Oro\Component\Layout\Block\Type\BaseType;
use Oro\Component\Layout\Block\Type\ContainerType;
use Oro\Component\Layout\BlockTypeInterface;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManagerInterface;
use Oro\Component\Layout\LayoutFactoryBuilderInterface;
use Oro\Component\Layout\Tests\Unit\BaseBlockTypeTestCase;
use Oro\Component\Layout\Tests\Unit\TestBlockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

final class PreloadFontsTypeTest extends BaseBlockTypeTestCase
{
    private ThemeConfigurationProvider&MockObject $themeConfigurationProvider;
    private ThemeManagerInterface&MockObject $themeManager;
    private BlockTypeInterface $type;

    #[\Override]
    protected function setUp(): void
    {
        $this->themeConfigurationProvider = $this->createMock(ThemeConfigurationProvider::class);
        $this->themeManager = $this->createMock(ThemeManagerInterface::class);

        parent::setUp();

        $this->type = $this->getBlockType('preload_fonts');
    }

    #[\Override]
    protected function initializeLayoutFactoryBuilder(LayoutFactoryBuilderInterface $layoutFactoryBuilder): void
    {
        parent::initializeLayoutFactoryBuilder($layoutFactoryBuilder);

        $preloadWebLink = new ConfigurableType();
        $preloadWebLink->setName('preload_web_link');
        $preloadWebLink->setParent(BaseType::NAME);
        $preloadWebLink->setOptionsConfig([
            'path' => ['required' => true],
            'preload_attributes' => ['required' => true, 'default' => []],
            'as' => ['required' => true],
            'crossorigin' => null,
        ]);

        $layoutFactoryBuilder
            ->addType(new PreloadFontsType($this->themeConfigurationProvider, $this->themeManager, '/build/_static/'))
            ->addType($preloadWebLink);
    }

    public function testGetParent(): void
    {
        self::assertSame(ContainerType::NAME, $this->type->getParent());
    }

    public function testGetName(): void
    {
        self::assertSame('preload_fonts', $this->type->getName());
    }

    public function testConfigureOptions(): void
    {
        self::assertSame(
            [
                'visible' => true,
                'preload_attributes' => ['as' => 'font'],
                'as' => 'font',
                'crossorigin' => 'anonymous'
            ],
            $this->resolveOptions($this->type->getName(), [])
        );
    }

    public function testConfigureOptionsWithPassedOptions(): void
    {
        self::assertSame(
            [
                'visible' => true,
                'preload_attributes' => ['noPush' => false, 'as' => 'img'],
                'as' => 'img',
                'crossorigin' => 'use-credentials'
            ],
            $this->resolveOptions('preload_fonts', [
                'preload_attributes' => ['noPush' => false, 'as' => 'img'],
                'as' => 'img',
                'crossorigin' => 'use-credentials'
            ])
        );
    }

    public function testBuildBlockNoThemeName(): void
    {
        $this->themeManager->expects(self::never())
            ->method('getThemeOption');

        $builder = $this->getBlockBuilder($this->type->getName());
        $blockView = $this->getBlockView($this->type->getName());

        self::assertEquals($blockView, $builder->getBlockView());
        self::assertSame([], $builder->getBlockView()->children);
    }

    public function testBuildBlockNoFonts(): void
    {
        $this->themeConfigurationProvider->expects(self::exactly(2))
            ->method('getThemeName')
            ->willReturn('default');

        $builder = $this->getBlockBuilder($this->type->getName());
        $blockView = $this->getBlockView($this->type->getName());

        self::assertEquals($blockView, $builder->getBlockView());
        self::assertSame([], $builder->getBlockView()->children);
    }

    public function testBuildBlock(): void
    {
        $this->themeConfigurationProvider->expects(self::exactly(2))
            ->method('getThemeName')
            ->willReturn('default');

        $this->themeManager->expects(self::exactly(2))
            ->method('getThemeOption')
            ->with('default', 'fonts')
            ->willReturn(self::getFontsData());

        $builder = $this->getBlockBuilder($this->type->getName());
        $blockView = $this->getBlockView($this->type->getName());

        self::assertEquals($blockView, $builder->getBlockView());

        self::checkPreloadWebLinkElement(
            $builder,
            'preload_fonts_id_preload_web_link0',
            '/build/_static/bundles/orofrontend/default/fonts/Plus_Jakarta_Sans/PlusJakartaSans-variable.woff2'
        );
        self::checkPreloadWebLinkElement(
            $builder,
            'preload_fonts_id_preload_web_link2',
            '/build/_static/bundles/orofrontend/default/fonts/bitter/Bitter-SemiBold.woff2'
        );
        self::checkPreloadWebLinkElement(
            $builder,
            'preload_fonts_id_preload_web_link3',
            '/build/_static/bundles/orofrontend/default/fonts/bitter/Bitter-SemiBold.woff'
        );
        self::checkPreloadWebLinkElement(
            $builder,
            'preload_fonts_id_preload_web_link4',
            '/build/_static/bundles/orofrontend/default/fonts/bitter/Bitter-Bold.woff2'
        );
        self::checkPreloadWebLinkElement(
            $builder,
            'preload_fonts_id_preload_web_link5',
            '/build/_static/bundles/orofrontend/default/fonts/bitter/Bitter-Bold.woff'
        );
    }

    private static function checkPreloadWebLinkElement(TestBlockBuilder $builder, string $name, string $path): void
    {
        self::assertNotNull($builder->getBlockView()->children[$name]);

        $vars = $builder->getBlockView()->children[$name]->vars;

        self::assertSame('font', $vars['as']);
        self::assertSame('anonymous', $vars['crossorigin']);
        self::assertSame(['as' => 'font', 'crossorigin' => 'anonymous'], $vars['preload_attributes']);
        self::assertSame($path, $vars['path']);
    }

    private static function getFontsData(): array
    {
        return [
            'check the sanitization of the ~ symbol and the removal of duplicate font paths' => [
                'preload' => true,
                'family' => 'Plus Jakarta Sans',
                'formats' => ['woff2'],
                'variants' => [
                    [
                        'path' => '~/bundles/orofrontend/default/fonts/Plus_Jakarta_Sans/PlusJakartaSans-variable',
                        'style' => 'normal',
                        'weight' => '300 700'
                    ], [
                        'path' => 'bundles/orofrontend/default/fonts/Plus_Jakarta_Sans/PlusJakartaSans-variable',
                        'style' => 'italic',
                        'weight' => '300 700'
                    ]
                ]
            ],
            'check the matching between variants and formats' => [
                'preload' => true,
                'family' => 'Bitter',
                'formats' => ['woff2', 'woff'],
                'variants' => [
                    [
                        'path' => '/bundles/orofrontend/default/fonts/bitter/Bitter-SemiBold',
                        'style' => 'normal',
                        'weight' => '600'
                    ], [
                        'path' => '/bundles/orofrontend/default/fonts/bitter/Bitter-Bold',
                        'style' => 'normal',
                        'weight' => '900'
                    ]
                ]
            ],
            'check that the font is not preloaded' => [
                'preload' => null,
                'family' => 'DM Mono',
                'formats' => ['woff2'],
                'variants' => [
                    [
                        'path' => '/bundles/orofrontend/default/fonts/dm-mono/DMMono-Regular',
                        'style' => 'normal',
                        'weight' => '400'
                    ]
                ]
            ],
        ];
    }
}
