<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Doctrine\Common\Cache\ChainCache;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;

class ScreensProvider implements ScreensProviderInterface
{
    /**
     * @internal
     */
    const SCREENS_CACHE_KEY = 'oro_frontend.provider.screens';

    /**
     * @var ThemeManager
     */
    private $themeManager;

    /**
     * @var ChainCache
     */
    private $cache;

    /**
     * @param ThemeManager $themeManager
     * @param ChainCache   $cache
     */
    public function __construct(ThemeManager $themeManager, ChainCache $cache)
    {
        $this->themeManager = $themeManager;
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function getScreens()
    {
        if ($this->cache->contains(static::SCREENS_CACHE_KEY)) {
            $screens = $this->cache->fetch(static::SCREENS_CACHE_KEY);
        } else {
            $screens = $this->collectScreens();

            $this->cache->save(static::SCREENS_CACHE_KEY, $screens);
        }

        return $screens;
    }

    /**
     * {@inheritDoc}
     */
    public function getScreen($screenName)
    {
        if ($this->hasScreen($screenName)) {
            return $this->getScreens()[$screenName];
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
