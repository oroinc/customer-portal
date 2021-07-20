<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Doctrine\Common\Cache\Cache;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;

/**
 * Provides configuration of screens defined for a particular layout theme, e.g. desktop, mobile, etc.
 */
class ScreensProvider implements ScreensProviderInterface
{
    private const SCREENS_CACHE_KEY = 'oro_frontend.provider.screens';

    /** @var ThemeManager */
    private $themeManager;

    /** @var Cache */
    private $cache;

    public function __construct(ThemeManager $themeManager, Cache $cache)
    {
        $this->themeManager = $themeManager;
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function getScreens()
    {
        $screens = $this->cache->fetch(self::SCREENS_CACHE_KEY);
        if (false === $screens) {
            $screens = $this->collectScreens();
            $this->cache->save(self::SCREENS_CACHE_KEY, $screens);
        }

        return $screens;
    }

    /**
     * {@inheritDoc}
     */
    public function getScreen($screenName)
    {
        $screens = $this->getScreens();
        if (array_key_exists($screenName, $screens)) {
            return $screens[$screenName];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasScreen($screenName)
    {
        $screens = $this->getScreens();

        return array_key_exists($screenName, $screens);
    }

    /**
     * @return array
     */
    private function collectScreens()
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
