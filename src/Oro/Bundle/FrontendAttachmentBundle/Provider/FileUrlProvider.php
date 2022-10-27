<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Provider;

use Oro\Bundle\ActionBundle\Provider\CurrentApplicationProviderInterface;
use Oro\Bundle\AttachmentBundle\Acl\FileAccessControlChecker;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Provider\FileApplicationsProvider;
use Oro\Bundle\AttachmentBundle\Provider\FileNameProviderInterface;
use Oro\Bundle\AttachmentBundle\Provider\FileUrlProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Decorates FileUrlProvider to bring both store front and backoffice functionality when working with files.
 */
class FileUrlProvider implements FileUrlProviderInterface
{
    private FileUrlProviderInterface $innerFileUrlProvider;

    private UrlGeneratorInterface $urlGenerator;

    private FileApplicationsProvider $fileApplicationsProvider;

    private CurrentApplicationProviderInterface $currentApplicationProvider;

    private FileAccessControlChecker $fileAccessControlChecker;

    private ConfigManager $configManager;

    private FileNameProviderInterface $filenameProvider;

    public function __construct(
        FileUrlProviderInterface $innerFileUrlProvider,
        UrlGeneratorInterface $urlGenerator,
        FileApplicationsProvider $fileApplicationsProvider,
        CurrentApplicationProviderInterface $currentApplicationProvider,
        FileAccessControlChecker $fileAccessControlChecker,
        ConfigManager $configManager,
        FilenameProviderInterface $filenameProvider
    ) {
        $this->innerFileUrlProvider = $innerFileUrlProvider;
        $this->urlGenerator = $urlGenerator;
        $this->fileApplicationsProvider = $fileApplicationsProvider;
        $this->currentApplicationProvider = $currentApplicationProvider;
        $this->fileAccessControlChecker = $fileAccessControlChecker;
        $this->configManager = $configManager;
        $this->filenameProvider = $filenameProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileUrl(
        File $file,
        string $action = self::FILE_ACTION_GET,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        if (!$this->isBackofficeApplication()) {
            return $this->urlGenerator->generate(
                'oro_frontend_attachment_get_file',
                [
                    'id' => $file->getId(),
                    'filename' => $this->filenameProvider->getFileName($file),
                    'action' => $action,
                ],
                $referenceType
            );
        }

        return $this->innerFileUrlProvider->getFileUrl($file, $action, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function getResizedImageUrl(
        File $file,
        int $width,
        int $height,
        string $format = '',
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        if ($this->isPublicOrFrontend($file)) {
            return $this->urlGenerator->generate(
                'oro_frontend_attachment_resize_image',
                [
                    'id' => $file->getId(),
                    'filename' => $this->filenameProvider->getResizedImageName($file, $width, $height, $format),
                    'width' => $width,
                    'height' => $height,
                ],
                $referenceType
            );
        }

        return $this->innerFileUrlProvider->getResizedImageUrl($file, $width, $height, $format, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilteredImageUrl(
        File $file,
        string $filterName,
        string $format = '',
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        if ($this->isPublicOrFrontend($file)) {
            return $this->urlGenerator->generate(
                'oro_frontend_attachment_filter_image',
                [
                    'id' => $file->getId(),
                    'filename' => $this->filenameProvider->getFilteredImageName($file, $filterName, $format),
                    'filter' => $filterName,
                    'format' => $format,
                ],
                $referenceType
            );
        }

        return $this->innerFileUrlProvider->getFilteredImageUrl($file, $filterName, $format, $referenceType);
    }

    private function isPublicOrFrontend(File $file): bool
    {
        $fileApplications = $this->fileApplicationsProvider->getFileApplications($file);
        if (in_array(FrontendCurrentApplicationProvider::COMMERCE_APPLICATION, $fileApplications, false)) {
            // File have to belong to frontend.
            return true;
        }

        if (!$this->fileAccessControlChecker->isCoveredByAcl($file)) {
            // File is publicly accessible.
            return true;
        }

        if (!$this->configManager->get('oro_frontend.guest_access_enabled')) {
            return false;
        }

        return !$this->isBackofficeApplication();
    }

    private function isBackofficeApplication(): bool
    {
        $currentApplication = $this->currentApplicationProvider->getCurrentApplication();

        // If no application is resolved via token we consider this is not frontend request.
        if (!$currentApplication) {
            return true;
        }

        // If we are currently in backoffice application.
        return $currentApplication === CurrentApplicationProviderInterface::DEFAULT_APPLICATION;
    }
}
