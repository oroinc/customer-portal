<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Provider\FileIconProvider as BaseFileIconProvider;
use Oro\Bundle\FrontendBundle\Provider\FileIconProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class FileIconProviderTest extends TestCase
{
    // Storefront icons
    private const string JPEG_ICON = 'jpeg-icon';
    private const string MP3_ICON = 'jpeg-icon';
    private const string DEFAULT_ICON = 'default-icon';
    private const string JPEG_EXT = 'jpeg';
    private const string MP3_EXT = 'mp3';
    private const string DEFAULT_EXT = 'default';
    private const array FILE_ICONS = [
        self::JPEG_EXT => self::JPEG_ICON,
        self::DEFAULT_EXT => self::DEFAULT_ICON
    ];
    private const string FALLBACK_ICON = 'add-note';

    // Backend icons
    private const string JPEG_FA_ICON = 'jpeg-fa-icon';
    private const string DEFAULT_FA_ICON = 'default-fa-icon';
    private const array FA_FILE_ICONS = [
        self::JPEG_EXT => self::JPEG_FA_ICON,
        self::DEFAULT_EXT => self::DEFAULT_FA_ICON
    ];

    private FrontendHelper&MockObject $frontendHelper;
    private CurrentThemeProvider&MockObject $currentThemeProvider;
    private ThemeManager&MockObject $themeManager;
    private FileIconProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->currentThemeProvider = $this->createMock(CurrentThemeProvider::class);
        $this->themeManager = $this->createMock(ThemeManager::class);

        $this->provider = new FileIconProvider(
            new BaseFileIconProvider(self::FA_FILE_ICONS),
            $this->frontendHelper,
            $this->currentThemeProvider,
            $this->themeManager
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

        self::assertSame($expectedIcon, $this->provider->getExtensionIconClass($file));
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

        self::assertSame(self::FALLBACK_ICON, $this->provider->getExtensionIconClass($file));
    }

    /**
     * @dataProvider getExtensionIconClassStorefrontDataProvider
     */
    public function testGetExtensionIconClassStorefront(string $fileExtension, string $expectedIcon): void
    {
        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendRequest')
            ->willReturn(true);

        $themeName = 'default';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($themeName);

        $this->themeManager->expects(self::once())
            ->method('getThemeConfigOption')
            ->with($themeName, 'icons')
            ->willReturn(['file_icons' => [$fileExtension => $expectedIcon]]);

        $file = new File();
        $file->setExtension($fileExtension);

        self::assertSame($expectedIcon, $this->provider->getExtensionIconClass($file));
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

        self::assertSame(self::FA_FILE_ICONS, $this->provider->getFileIcons());
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

    public function testGetFileIconsStorefront(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $themeName = 'default';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($themeName);

        $fileIcons = \array_merge(self::FILE_ICONS, [self::MP3_EXT => self::MP3_ICON]);

        $this->themeManager->expects(self::once())
            ->method('getThemeConfigOption')
            ->with($themeName, 'icons')
            ->willReturn(['file_icons' => $fileIcons]);

        self::assertSame($fileIcons, $this->provider->getFileIcons());
    }
}
