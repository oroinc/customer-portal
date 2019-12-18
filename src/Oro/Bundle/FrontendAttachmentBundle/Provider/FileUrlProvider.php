<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Provider;

use Oro\Bundle\ActionBundle\Provider\CurrentApplicationProviderInterface;
use Oro\Bundle\AttachmentBundle\Acl\FileAccessControlChecker;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Provider\FileApplicationsProvider;
use Oro\Bundle\AttachmentBundle\Provider\FileNameProviderInterface;
use Oro\Bundle\AttachmentBundle\Provider\FileUrlProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Decorates FileUrlProvider to bring both store front and backoffice functionality when working with files.
 */
class FileUrlProvider implements FileUrlProviderInterface
{
    /** @var FileUrlProviderInterface */
    private $innerFileUrlProvider;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var FileApplicationsProvider */
    private $fileApplicationsProvider;

    /** @var CurrentApplicationProviderInterface */
    private $currentApplicationProvider;

    /** @var FileAccessControlChecker */
    private $fileAccessControlChecker;

    /** @var ConfigManager */
    private $configManager;

    /**
     * @var FileNameProviderInterface
     */
    private $filenameProvider;

    /**
     * @param FileUrlProviderInterface $innerFileUrlProvider
     * @param UrlGeneratorInterface $urlGenerator
     * @param FileApplicationsProvider $fileApplicationsProvider
     * @param CurrentApplicationProviderInterface $currentApplicationProvider
     * @param FileAccessControlChecker $fileAccessControlChecker
     */
    public function __construct(
        FileUrlProviderInterface $innerFileUrlProvider,
        UrlGeneratorInterface $urlGenerator,
        FileApplicationsProvider $fileApplicationsProvider,
        CurrentApplicationProviderInterface $currentApplicationProvider,
        FileAccessControlChecker $fileAccessControlChecker
    ) {
        $this->innerFileUrlProvider = $innerFileUrlProvider;
        $this->urlGenerator = $urlGenerator;
        $this->fileApplicationsProvider = $fileApplicationsProvider;
        $this->currentApplicationProvider = $currentApplicationProvider;
        $this->fileAccessControlChecker = $fileAccessControlChecker;
    }

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    /**
     * @param FileNameProviderInterface $filenameProvider
     */
    public function setFileNameProvider(FilenameProviderInterface $filenameProvider): void
    {
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
        if ($this->isPublicOrFrontend($file)) {
            return $this->urlGenerator->generate(
                'oro_frontend_attachment_get_file',
                ['id' => $file->getId(), 'filename' => $file->getFilename(), 'action' => $action],
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
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        if ($this->isPublicOrFrontend($file)) {
            return $this->urlGenerator->generate(
                'oro_frontend_attachment_resize_image',
                [
                    'id' => $file->getId(),
                    'filename' => $this->filenameProvider->getFileName($file),
                    'width' => $width,
                    'height' => $height,
                ],
                $referenceType
            );
        }

        return $this->innerFileUrlProvider->getResizedImageUrl($file, $width, $height, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilteredImageUrl(
        File $file,
        string $filterName,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        if ($this->isPublicOrFrontend($file)) {
            return $this->urlGenerator->generate(
                'oro_frontend_attachment_filter_image',
                [
                    'id' => $file->getId(),
                    'filename' => $this->filenameProvider->getFileName($file),
                    'filter' => $filterName,
                ],
                $referenceType
            );
        }

        return $this->innerFileUrlProvider->getFilteredImageUrl($file, $filterName, $referenceType);
    }

    /**
     * @param File $file
     *
     * @return bool
     */
    private function isPublicOrFrontend(File $file): bool
    {
        $fileApplications = $this->fileApplicationsProvider->getFileApplications($file);
        if (!\in_array(CurrentApplicationProviderInterface::DEFAULT_APPLICATION, $fileApplications, false)) {
            // File does not belong to backoffice.
            return true;
        }

        if (!$this->configManager->get('oro_frontend.guest_access_enabled')) {
            return false;
        }

        if (!$this->fileAccessControlChecker->isCoveredByAcl($file)) {
            // File is publicly accessible.
            return true;
        }

        $currentApplication = $this->currentApplicationProvider->getCurrentApplication();

        // If no application is resolved via token we consider this is not frontend request.
        if (!$currentApplication) {
            return false;
        }

        // If we are not currently in backoffice application.
        return $currentApplication
            !== CurrentApplicationProviderInterface::DEFAULT_APPLICATION;
    }
}
