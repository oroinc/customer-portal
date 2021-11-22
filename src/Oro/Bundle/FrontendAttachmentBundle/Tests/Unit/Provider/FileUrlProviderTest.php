<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Tests\Unit\Provider;

use Oro\Bundle\ActionBundle\Provider\CurrentApplicationProviderInterface;
use Oro\Bundle\AttachmentBundle\Acl\FileAccessControlChecker;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Provider\FileApplicationsProvider;
use Oro\Bundle\AttachmentBundle\Provider\FileNameProviderInterface;
use Oro\Bundle\AttachmentBundle\Provider\FileUrlProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendAttachmentBundle\Provider\FileUrlProvider;
use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class FileUrlProviderTest extends \PHPUnit\Framework\TestCase
{
    private const FILENAME = 'sample-filename';
    private const FILE_ID = 1;
    private const ACTION = 'sample-action';
    private const REFERENCE_TYPE = 1;
    private const URL = 'sample-url';
    private const FILTER = 'sample-filter';
    private const WIDTH = 10;
    private const HEIGHT = 20;

    /** @var FileUrlProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $innerFileUrlProvider;

    /** @var UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $urlGenerator;

    /** @var FileApplicationsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $fileApplicationsProvider;

    /** @var CurrentApplicationProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $currentApplicationProvider;

    /** @var FileAccessControlChecker|\PHPUnit\Framework\MockObject\MockObject */
    private $fileAccessControlChecker;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var FileNameProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $filenameProvider;

    /** @var FileUrlProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->innerFileUrlProvider = $this->createMock(FileUrlProviderInterface::class);
        $this->fileApplicationsProvider = $this->createMock(FileApplicationsProvider::class);
        $this->currentApplicationProvider = $this->createMock(CurrentApplicationProviderInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->fileAccessControlChecker = $this->createMock(FileAccessControlChecker::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->filenameProvider = $this->createMock(FileNameProviderInterface::class);

        $this->provider = new FileUrlProvider(
            $this->innerFileUrlProvider,
            $this->urlGenerator,
            $this->fileApplicationsProvider,
            $this->currentApplicationProvider,
            $this->fileAccessControlChecker,
            $this->configManager,
            $this->filenameProvider
        );
    }

    public function testGetFileUrlWhenNotFrontend(): void
    {
        $file = new File();

        $this->currentApplicationProvider->expects($this->once())
            ->method('getCurrentApplication')
            ->willReturn(CurrentApplicationProviderInterface::DEFAULT_APPLICATION);

        $this->innerFileUrlProvider->expects(self::once())
            ->method('getFileUrl')
            ->with($file, self::ACTION, self::REFERENCE_TYPE)
            ->willReturn(self::URL);

        self::assertEquals(self::URL, $this->provider->getFileUrl($file, self::ACTION, self::REFERENCE_TYPE));
    }

    private function mockGuestAccessMode(bool $enabled): void
    {
        $this->configManager
            ->method('get')
            ->with('oro_frontend.guest_access_enabled')
            ->willReturn($enabled);
    }

    private function mockCoveredByAcl(File $file, bool $isCoveredByAcl): void
    {
        $this->fileAccessControlChecker
            ->method('isCoveredByAcl')
            ->with($file)
            ->willReturn($isCoveredByAcl);
    }

    /**
     * @param array $appNames
     * @param string|null $currentApplication
     */
    private function mockApplications(array $appNames, $currentApplication): void
    {
        $this->fileApplicationsProvider
            ->method('getFileApplications')
            ->willReturn($appNames);

        $this->currentApplicationProvider
            ->method('getCurrentApplication')
            ->willReturn($currentApplication);
    }

    public function testGetResizedImageUrlWhenNotFrontend(): void
    {
        $file = new File();
        $this->mockGuestAccessMode(true);
        $this->mockCoveredByAcl($file, true);

        $this->mockApplications(
            [CurrentApplicationProviderInterface::DEFAULT_APPLICATION],
            CurrentApplicationProviderInterface::DEFAULT_APPLICATION
        );

        $this->innerFileUrlProvider->expects(self::once())
            ->method('getResizedImageUrl')
            ->with($file, self::WIDTH, self::HEIGHT, self::REFERENCE_TYPE)
            ->willReturn(self::URL);

        self::assertEquals(
            self::URL,
            $this->provider->getResizedImageUrl($file, self::WIDTH, self::HEIGHT, self::REFERENCE_TYPE)
        );
    }

    public function testFilteredImageUrlWhenNotFrontend(): void
    {
        $file = new File();
        $this->mockGuestAccessMode(true);
        $this->mockCoveredByAcl($file, true);

        $this->mockApplications(
            [CurrentApplicationProviderInterface::DEFAULT_APPLICATION],
            CurrentApplicationProviderInterface::DEFAULT_APPLICATION
        );

        $this->innerFileUrlProvider->expects(self::once())
            ->method('getFilteredImageUrl')
            ->with($file, self::FILTER, self::REFERENCE_TYPE)
            ->willReturn(self::URL);

        self::assertEquals(
            self::URL,
            $this->provider->getFilteredImageUrl($file, self::FILTER, self::REFERENCE_TYPE)
        );
    }

    public function testFilteredImageUrlWhenNoApplicationDefined(): void
    {
        $this->mockGuestAccessMode(true);
        $this->mockCoveredByAcl($file = new File(), true);

        $this->mockApplications([CurrentApplicationProviderInterface::DEFAULT_APPLICATION], null);

        $this->innerFileUrlProvider->expects(self::once())
            ->method('getFilteredImageUrl')
            ->with($file, self::FILTER, self::REFERENCE_TYPE)
            ->willReturn(self::URL);

        self::assertEquals(
            self::URL,
            $this->provider->getFilteredImageUrl($file, self::FILTER, self::REFERENCE_TYPE)
        );
    }

    /**
     * @dataProvider frontendOrPublicDataProvider
     */
    public function testGetFileUrlWhenFrontend(array $fileApplications, bool $isCoveredByAcl): void
    {
        $file = $this->getFile(self::FILE_ID, self::FILENAME);

        $this->currentApplicationProvider->expects($this->once())
            ->method('getCurrentApplication')
            ->willReturn(FrontendCurrentApplicationProvider::COMMERCE_APPLICATION);

        $this->urlGenerator
            ->method('generate')
            ->with(
                'oro_frontend_attachment_get_file',
                [
                    'id' => self::FILE_ID,
                    'filename' => self::FILENAME,
                    'action' => self::ACTION,
                ],
                self::REFERENCE_TYPE
            )
            ->willReturn(self::URL);

        self::assertEquals(
            self::URL,
            $this->provider->getFileUrl($file, self::ACTION, self::REFERENCE_TYPE)
        );
    }

    private function getFile(int $id = null, string $filename = ''): File
    {
        $file = $this->createMock(File::class);
        $file
            ->method('getId')
            ->willReturn($id);

        $file
            ->method('getFilename')
            ->willReturn($filename);

        return $file;
    }

    public function frontendOrPublicDataProvider(): array
    {
        return [
            [
                'fileApplications' => [
                    FrontendCurrentApplicationProvider::DEFAULT_APPLICATION,
                    FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
                ],
                'isCoveredByAcl' => true,
            ],
            [
                'fileApplications' => ['sample-app'],
                'isCoveredByAcl' => true,
            ],
            [
                'fileApplications' => [FrontendCurrentApplicationProvider::DEFAULT_APPLICATION],
                'isCoveredByAcl' => false,
            ],
            [
                'fileApplications' => [
                    FrontendCurrentApplicationProvider::DEFAULT_APPLICATION,
                    FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
                ],
                'isCoveredByAcl' => false,
            ],
        ];
    }

    /**
     * @dataProvider frontendOrPublicDataProvider
     */
    public function testResizedImageUrlWhenFrontend(array $fileApplications, bool $isCoveredByAcl): void
    {
        $this->mockGuestAccessMode(true);
        $this->mockCoveredByAcl($file = $this->getFile(self::FILE_ID, self::FILENAME), $isCoveredByAcl);

        $this->filenameProvider->expects($this->once())
            ->method('getFileName')
            ->with($file)
            ->willReturn(self::FILENAME);
        $this->mockApplications($fileApplications, FrontendCurrentApplicationProvider::COMMERCE_APPLICATION);

        $this->urlGenerator
            ->method('generate')
            ->with(
                'oro_frontend_attachment_resize_image',
                [
                    'id' => self::FILE_ID,
                    'filename' => self::FILENAME,
                    'width' => self::WIDTH,
                    'height' => self::HEIGHT,
                ],
                self::REFERENCE_TYPE
            )
            ->willReturn(self::URL);

        self::assertEquals(
            self::URL,
            $this->provider->getResizedImageUrl($file, self::WIDTH, self::HEIGHT, self::REFERENCE_TYPE)
        );
    }

    /**
     * @dataProvider frontendOrPublicDataProvider
     */
    public function testGetFilteredImageUrlWhenFrontend(array $fileApplications, bool $isCoveredByAcl): void
    {
        $this->mockGuestAccessMode(true);
        $this->mockCoveredByAcl($file = $this->getFile(self::FILE_ID, self::FILENAME), $isCoveredByAcl);

        $this->filenameProvider->expects($this->once())
            ->method('getFileName')
            ->with($file)
            ->willReturn(self::FILENAME);
        $this->mockApplications($fileApplications, FrontendCurrentApplicationProvider::COMMERCE_APPLICATION);

        $this->urlGenerator
            ->method('generate')
            ->with(
                'oro_frontend_attachment_filter_image',
                [
                    'id' => self::FILE_ID,
                    'filename' => self::FILENAME,
                    'filter' => self::FILTER,
                ],
                self::REFERENCE_TYPE
            )
            ->willReturn(self::URL);

        self::assertEquals(
            self::URL,
            $this->provider->getFilteredImageUrl($file, self::FILTER, self::REFERENCE_TYPE)
        );
    }

    /**
     * @dataProvider frontendOrPublicWhenGuestModeDisabledDataProvider
     */
    public function testResizedImageUrlWhenGuestModeDisabled(array $fileApplications): void
    {
        $this->mockGuestAccessMode(false);
        $this->mockCoveredByAcl($file = $this->getFile(self::FILE_ID, self::FILENAME), true);

        $this->mockApplications($fileApplications, FrontendCurrentApplicationProvider::COMMERCE_APPLICATION);

        $this->urlGenerator->expects(self::never())
            ->method('generate');

        self::assertEquals(
            '',
            $this->provider->getResizedImageUrl($file, self::WIDTH, self::HEIGHT, self::REFERENCE_TYPE)
        );
    }

    public function frontendOrPublicWhenGuestModeDisabledDataProvider(): array
    {
        return [
            [
                'fileApplications' => [
                    FrontendCurrentApplicationProvider::DEFAULT_APPLICATION,
                    FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
                ],
            ],
            [
                'fileApplications' => [
                    FrontendCurrentApplicationProvider::DEFAULT_APPLICATION
                ],
            ],
        ];
    }

    /**
     * @dataProvider frontendOrPublicWhenGuestModeDisabledDataProvider
     */
    public function testGetFilteredImageUrlWhenGuestModeDisabled(array $fileApplications): void
    {
        $this->mockGuestAccessMode(false);
        $this->mockCoveredByAcl($file = $this->getFile(self::FILE_ID, self::FILENAME), true);

        $this->mockApplications($fileApplications, FrontendCurrentApplicationProvider::COMMERCE_APPLICATION);

        $this->urlGenerator->expects(self::never())
            ->method('generate');

        self::assertEquals(
            '',
            $this->provider->getFilteredImageUrl($file, self::FILTER, self::REFERENCE_TYPE)
        );
    }
}
