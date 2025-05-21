<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\AttachmentBundle\Entity\FileExtensionInterface;
use Oro\Bundle\AttachmentBundle\Provider\FileIconProvider as BaseFileIconProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Component\Layout\Exception\NotRequestContextRuntimeException;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;

/**
 * Provides file type icon for the given file entity.
 */
class FileIconProvider extends BaseFileIconProvider
{
    private const string FALLBACK_ICON = 'add-note';

    public function __construct(
        private BaseFileIconProvider $innerFileIconProvider,
        private FrontendHelper $frontendHelper,
        private CurrentThemeProvider $currentThemeProvider,
        private ThemeManager $themeManager
    ) {
        parent::__construct([]);
    }

    #[\Override]
    public function getExtensionIconClass(FileExtensionInterface $entity): string
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return $this->innerFileIconProvider->getExtensionIconClass($entity);
        }

        $icons = $this->getFileIcons();

        return $icons[$entity->getExtension()] ?? $icons['default'] ?? self::FALLBACK_ICON;
    }

    #[\Override]
    public function getFileIcons(): array
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return $this->innerFileIconProvider->getFileIcons();
        }

        $themeName = $this->getCurrentThemeName();
        if ($themeName === null) {
            return [];
        }

        $icons = $this->themeManager->getThemeConfigOption($themeName, 'icons');
        return $icons['file_icons'] ?? [];
    }

    private function getCurrentThemeName(): ?string
    {
        try {
            $currentThemeName = $this->currentThemeProvider->getCurrentThemeId();
        } catch (NotRequestContextRuntimeException $exception) {
            $currentThemeName = null;
        }

        return $currentThemeName;
    }
}
