<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Provider\FileIconProvider as BaseFileIconProvider;
use Oro\Bundle\CacheBundle\Adapter\ChainAdapter;
use Oro\Bundle\FrontendBundle\Provider\FileIconProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class FileIconProviderTest extends TestCase
{
    // Storefront icons
    private const JPEG_ICON = 'jpeg-icon';
    private const MP3_ICON = 'jpeg-icon';
    private const DEFAULT_ICON = 'default-icon';
    private const JPEG_EXT = 'jpeg';
    private const MP3_EXT = 'mp3';
    private const DEFAULT_EXT = 'default';
    private const FILE_ICONS = [self::JPEG_EXT => self::JPEG_ICON, self::DEFAULT_EXT => self::DEFAULT_ICON];
    private const FALLBACK_ICON = 'add-note';

    // Backend icons
    private const JPEG_FA_ICON = 'jpeg-fa-icon';
    private const DEFAULT_FA_ICON = 'default-fa-icon';
    private const FA_FILE_ICONS = [self::JPEG_EXT => self::JPEG_FA_ICON, self::DEFAULT_EXT => self::DEFAULT_FA_ICON];

    private FrontendHelper|MockObject $frontendHelper;

    private CurrentThemeProvider|MockObject $currentThemeProvider;

    private ThemeManager|MockObject $themeManager;

    private CacheInterface|CacheItemPoolInterface|MockObject $cache;

    private FileIconProvider $provider;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->currentThemeProvider = $this->createMock(CurrentThemeProvider::class);
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->cache = $this->createMock(ChainAdapter::class);

        $this->provider = new FileIconProvider(
            new BaseFileIconProvider(self::FA_FILE_ICONS),
            $this->frontendHelper,
            $this->currentThemeProvider,
            $this->themeManager,
            $this->cache
        );
    }

    /**
     * @dataProvider getExtensionIconClassBackendDataProvider
     */
    public function testGetExtensionIconClassBackend(string $fileExtension, string $expectedIcon): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $file = new File();
        $file->setExtension($fileExtension);

        self::assertEquals($expectedIcon, $this->provider->getExtensionIconClass($file));
    }

    public function getExtensionIconClassBackendDataProvider(): array
    {
        return [
            [
                'fileExtension' => 'unknown',
                'expectedIcon' => self::DEFAULT_FA_ICON,
            ],
            [
                'fileExtension' =>  self::DEFAULT_EXT,
                'expectedIcon' => self::DEFAULT_FA_ICON,
            ],
            [
                'fileExtension' => self::JPEG_EXT,
                'expectedIcon' => self::JPEG_FA_ICON,
            ],
        ];
    }

    public function testGetExtensionIconClassStorefrontNoTheme(): void
    {
        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn(null);

        $file = (new File())->setExtension(self::DEFAULT_EXT);

        self::assertEquals(self::FALLBACK_ICON, $this->provider->getExtensionIconClass($file));
    }

    /**
     * @dataProvider getExtensionIconClassStorefrontDataProvider
     */
    public function testGetExtensionIconClassStorefrontNoCache(string $fileExtension, string $expectedIcon): void
    {
        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendRequest')
            ->willReturn(true);

        $themeName = 'default';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($themeName);

        $this->cache->expects(self::once())
            ->method('get')
            ->with('oro_frontend.provider.file_icons_mapping.theme.' . $themeName)
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);

                return $callback($item);
            });

        $themeHierarchy = [
            $this->getTheme(['file_icons' => self::FILE_ICONS]),
            $this->getTheme([]),
            $this->getTheme(
                ['file_icons' => [self::DEFAULT_EXT => self::DEFAULT_ICON, self::MP3_EXT => self::MP3_ICON]]
            ),
        ];

        $this->themeManager->expects(self::once())
            ->method('getThemesHierarchy')
            ->willReturn($themeHierarchy);

        $file = new File();
        $file->setExtension($fileExtension);

        self::assertEquals($expectedIcon, $this->provider->getExtensionIconClass($file));
    }

    /**
     * @dataProvider getExtensionIconClassStorefrontDataProvider
     */
    public function testGetExtensionIconClassStorefrontFromCache(string $fileExtension, string $expectedIcon): void
    {
        $fileIcons = array_merge(self::FILE_ICONS, [self::MP3_EXT => self::MP3_ICON]);
        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendRequest')
            ->willReturn(true);

        $themeName = 'default';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($themeName);

        $this->cache->expects(self::once())
            ->method('get')
            ->with('oro_frontend.provider.file_icons_mapping.theme.' . $themeName)
            ->willReturn($fileIcons);

        $this->themeManager->expects(self::never())
            ->method(self::anything());

        $file = new File();
        $file->setExtension($fileExtension);

        self::assertEquals($expectedIcon, $this->provider->getExtensionIconClass($file));
    }

    public function getExtensionIconClassStorefrontDataProvider(): array
    {
        return [
            [
                'fileExtension' => 'unknown',
                'expectedIcon' => self::DEFAULT_ICON,
            ],
            [
                'fileExtension' =>  self::DEFAULT_EXT,
                'expectedIcon' => self::DEFAULT_ICON,
            ],
            [
                'fileExtension' => self::JPEG_EXT,
                'expectedIcon' => self::JPEG_ICON,
            ],
            [
                'fileExtension' => self::MP3_EXT,
                'expectedIcon' => self::MP3_ICON,
            ],
        ];
    }

    public function testGetFileIconsBackend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        self::assertEquals(self::FA_FILE_ICONS, $this->provider->getFileIcons());
    }

    public function testGetFileIconsStorefrontNoTheme(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn(null);

        self::assertEmpty($this->provider->getFileIcons());
    }

    public function testGetFileIconsStorefrontNoCache(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $themeName = 'default';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($themeName);

        $this->cache->expects(self::once())
            ->method('get')
            ->with('oro_frontend.provider.file_icons_mapping.theme.' . $themeName)
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);

                return $callback($item);
            });

        $themeHierarchy = [
            $this->getTheme(['file_icons' => self::FILE_ICONS]),
            $this->getTheme([]),
            $this->getTheme(
                ['file_icons' => [self::DEFAULT_EXT => self::DEFAULT_ICON, self::MP3_EXT => self::MP3_ICON]]
            ),
        ];

        $this->themeManager->expects(self::once())
            ->method('getThemesHierarchy')
            ->willReturn($themeHierarchy);

        self::assertEquals(
            array_merge(self::FILE_ICONS, [self::MP3_EXT => self::MP3_ICON]),
            $this->provider->getFileIcons()
        );
    }

    public function testGetFileIconsStorefrontFromCache(): void
    {
        $fileIcons = array_merge(self::FILE_ICONS, [self::MP3_EXT => self::MP3_ICON]);
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $themeName = 'default';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($themeName);

        $this->cache->expects(self::once())
            ->method('get')
            ->with('oro_frontend.provider.file_icons_mapping.theme.' . $themeName)
            ->willReturn($fileIcons);

        $this->themeManager->expects(self::never())
            ->method(self::anything());

        self::assertEquals($fileIcons, $this->provider->getFileIcons());
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
