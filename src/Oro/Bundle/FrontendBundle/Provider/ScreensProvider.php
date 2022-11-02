<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Provides configuration of screens defined for a particular layout theme, e.g. desktop, mobile, etc.
 */
class ScreensProvider implements ScreensProviderInterface
{
    private const SCREENS_CACHE_KEY = 'oro_frontend.provider.screens';

    private ThemeManager $themeManager;
    private CacheInterface $cache;

    public function __construct(ThemeManager $themeManager, CacheInterface $cache)
    {
        $this->themeManager = $themeManager;
        $this->cache = $cache;
    }

    public function getScreens(): array
    {
        return $this->cache->get(self::SCREENS_CACHE_KEY, function () {
            return $this->collectScreens();
        });
    }

    public function getScreen($screenName): ?array
    {
        $screens = $this->getScreens();
        if (array_key_exists($screenName, $screens)) {
            return $screens[$screenName];
        }

        return null;
    }

    public function hasScreen($screenName): bool
    {
        $screens = $this->getScreens();

        return array_key_exists($screenName, $screens);
    }

    private function collectScreens(): array
    {
        $themes = $this->themeManager->getAllThemes();
        $screens = [];
        foreach ($themes as $theme) {
            $themeConfig = $theme->getConfig();
            if (array_key_exists('screens', $themeConfig)) {
                $screens = array_merge($screens, $themeConfig['screens']);
            }
        }

        return $screens;
    }
}
