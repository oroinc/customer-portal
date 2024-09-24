<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\AttachmentBundle\Entity\FileExtensionInterface;
use Oro\Bundle\AttachmentBundle\Provider\FileIconProvider as BaseFileIconProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Component\Layout\Exception\NotRequestContextRuntimeException;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Provides file type icon for the given file entity.
 */
class FileIconProvider extends BaseFileIconProvider
{
    private const FALLBACK_ICON = 'add-note';
    private const CACHE_KEY = 'oro_frontend.provider.file_icons_mapping';

    public function __construct(
        private BaseFileIconProvider $innerFileIconProvider,
        private FrontendHelper $frontendHelper,
        private CurrentThemeProvider $currentThemeProvider,
        private ThemeManager $themeManager,
        private CacheInterface&CacheItemPoolInterface $cache
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

        return $this->cache->get(self::CACHE_KEY . '.theme.' . $themeName, function () use ($themeName) {
            $themes = $this->themeManager->getThemesHierarchy($themeName);

            return $this->collectFileIconsMapping($themes);
        });
    }

    /**
     * @param array<Theme> $themes
     *
     * @return array<array<string,string>>
     */
    private function collectFileIconsMapping(array $themes): array
    {
        $fileIconsMapping = [];
        foreach ($themes as $theme) {
            $iconsConfig = $theme->getConfigByKey('icons', []);
            if (!empty($iconsConfig['file_icons'])) {
                $fileIconsMapping[] = $iconsConfig['file_icons'];
            }
        }

        return array_merge(...$fileIconsMapping);
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

    public function reset(): void
    {
        $this->cache->clear(self::CACHE_KEY);
    }
}
