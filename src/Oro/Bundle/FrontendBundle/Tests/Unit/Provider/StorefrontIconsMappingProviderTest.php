<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\CacheBundle\Adapter\ChainAdapter;
use Oro\Bundle\FrontendBundle\Provider\StorefrontIconsMappingProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class StorefrontIconsMappingProviderTest extends TestCase
{
    private ThemeManager|MockObject $themeManager;

    private CacheInterface|CacheItemPoolInterface|MockObject $cache;

    private StorefrontIconsMappingProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->cache = $this->createMock(ChainAdapter::class);
        $this->themeManager = $this->createMock(ThemeManager::class);

        $this->provider = new StorefrontIconsMappingProvider($this->themeManager, $this->cache);
    }

    public function testGetIconsMappingForAllThemesWhenNoCache(): void
    {
        $this->cache->expects(self::once())
            ->method('get')
            ->with('oro_frontend.provider.icons_mapping')
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        $allThemes = [
            $this->getTheme(
                ['fa_to_svg' => ['fa-first-icon' => 'svg-first-icon', 'fa-second-icon' => 'svf-second-icon']]
            ),
            $this->getTheme([]),
            $this->getTheme(
                ['fa_to_svg' => ['fa-first-icon' => 'svg-first-icon', 'fa-third-icon' => 'svg-third-icon']]
            ),
        ];
        $this->themeManager
            ->expects(self::once())
            ->method('getAllThemes')
            ->willReturn($allThemes);

        $iconsMapping = $this->provider->getIconsMappingForAllThemes();

        self::assertEquals([
            'fa-first-icon' => 'svg-first-icon',
            'fa-second-icon' => 'svf-second-icon',
            'fa-third-icon' => 'svg-third-icon',
        ], $iconsMapping);
    }

    public function testGetIconsMappingForAllThemesWithGroupsWhenNoCache(): void
    {
        $this->cache->expects(self::once())
            ->method('get')
            ->with('oro_frontend.provider.icons_mapping.all.sample_group1|sample_group2')
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        $allThemes = [
            $this->getTheme(
                ['fa_to_svg' => ['fa-first-icon' => 'svg-first-icon', 'fa-second-icon' => 'svf-second-icon']]
            ),
            $this->getTheme([]),
            $this->getTheme(
                ['fa_to_svg' => ['fa-first-icon' => 'svg-first-icon', 'fa-third-icon' => 'svg-third-icon']]
            ),
        ];

        $themeGroups = ['sample_group1', 'sample_group2'];
        $this->themeManager
            ->expects(self::once())
            ->method('getAllThemes')
            ->with($themeGroups)
            ->willReturn($allThemes);

        $iconsMapping = $this->provider->getIconsMappingForAllThemes($themeGroups);

        self::assertEquals([
            'fa-first-icon' => 'svg-first-icon',
            'fa-second-icon' => 'svf-second-icon',
            'fa-third-icon' => 'svg-third-icon',
        ], $iconsMapping);
    }

    public function testGetIconsMappingForAllThemesFromCache(): void
    {
        $iconsMapping = ['fa-sample-icon' => 'svg-sample-icon'];
        $this->cache->expects(self::once())
            ->method('get')
            ->with('oro_frontend.provider.icons_mapping')
            ->willReturn($iconsMapping);

        $this->themeManager
            ->expects(self::never())
            ->method(self::anything());

        $screens = $this->provider->getIconsMappingForAllThemes();

        self::assertEquals($iconsMapping, $screens);
    }

    public function testGetIconsMappingForAllThemesWithSingleGroupFromCache(): void
    {
        $iconsMapping = ['fa-sample-icon' => 'svg-sample-icon'];
        $themeGroups = 'sample-group';
        $this->cache->expects(self::once())
            ->method('get')
            ->with('oro_frontend.provider.icons_mapping.all.' . $themeGroups)
            ->willReturn($iconsMapping);

        $this->themeManager
            ->expects(self::never())
            ->method(self::anything());

        $screens = $this->provider->getIconsMappingForAllThemes($themeGroups);

        self::assertEquals($iconsMapping, $screens);
    }

    public function testGetIconsMappingForThemeWhenNoCache(): void
    {
        $themeName = 'sample_theme';

        $this->cache->expects(self::once())
            ->method('get')
            ->with('oro_frontend.provider.icons_mapping.theme.' . $themeName)
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        $themeHierarchy = [
            $this->getTheme(
                ['fa_to_svg' => ['fa-first-icon' => 'svg-first-icon', 'fa-second-icon' => 'svf-second-icon']]
            ),
            $this->getTheme([]),
            $this->getTheme(
                ['fa_to_svg' => ['fa-first-icon' => 'svg-first-icon', 'fa-third-icon' => 'svg-third-icon']]
            ),
        ];

        $this->themeManager
            ->expects(self::once())
            ->method('getThemesHierarchy')
            ->willReturn($themeHierarchy);

        $iconsMapping = $this->provider->getIconsMappingForTheme($themeName);

        self::assertEquals([
            'fa-first-icon' => 'svg-first-icon',
            'fa-second-icon' => 'svf-second-icon',
            'fa-third-icon' => 'svg-third-icon',
        ], $iconsMapping);
    }

    public function testGetIconsMappingForThemeFromCache(): void
    {
        $themeName = 'sample_theme';
        $iconsMapping = ['fa-sample-icon' => 'svg-sample-icon'];
        $this->cache->expects(self::once())
            ->method('get')
            ->with('oro_frontend.provider.icons_mapping.theme.' . $themeName)
            ->willReturn($iconsMapping);

        $this->themeManager
            ->expects(self::never())
            ->method(self::anything());

        $screens = $this->provider->getIconsMappingForTheme($themeName);

        self::assertEquals($iconsMapping, $screens);
    }

    private function getTheme(array $iconsConfig): Theme
    {
        $theme = $this->createMock(Theme::class);
        $theme->expects(self::once())
            ->method('getConfigByKey')
            ->with('icons')
            ->willReturn($iconsConfig);

        return $theme;
    }
}
