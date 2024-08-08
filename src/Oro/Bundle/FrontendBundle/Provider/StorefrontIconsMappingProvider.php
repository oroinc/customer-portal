<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Provides storefront icons mapping.
 */
class StorefrontIconsMappingProvider implements ResetInterface
{
    private const CACHE_KEY = 'oro_frontend.provider.icons_mapping';

    public function __construct(
        private ThemeManager $themeManager,
        private CacheInterface&CacheItemPoolInterface $cache
    ) {
    }

    /**
     * @param string $themeName
     *
     * @return array<array<string,string>>
     */
    public function getIconsMappingForTheme(string $themeName): array
    {
        return $this->cache->get(self::CACHE_KEY . '.theme.' . $themeName, function () use ($themeName) {
            $themes = $this->themeManager->getThemesHierarchy($themeName);

            return $this->collectIconsMapping($themes);
        });
    }

    public function getIconsMappingForAllThemes(array|string|null $themeGroups = null): array
    {
        $themeGroups = (array)$themeGroups;
        $cacheKey = $themeGroups ? self::CACHE_KEY . '.all.' . implode('|', $themeGroups) : self::CACHE_KEY;
        return $this->cache->get($cacheKey, function () use ($themeGroups) {
            $themes = $this->themeManager->getAllThemes($themeGroups);

            return $this->collectIconsMapping($themes);
        });
    }

    /**
     * @param array<Theme> $themes
     *
     * @return array<array<string,string>>
     */
    private function collectIconsMapping(array $themes): array
    {
        $iconsMapping = [];
        foreach ($themes as $theme) {
            $iconsConfig = $theme->getConfigByKey('icons', []);
            if (!empty($iconsConfig['fa_to_svg'])) {
                $iconsMapping[] = $iconsConfig['fa_to_svg'];
            }
        }

        return array_merge(...$iconsMapping);
    }

    public function reset(): void
    {
        $this->cache->clear(self::CACHE_KEY);
    }
}
