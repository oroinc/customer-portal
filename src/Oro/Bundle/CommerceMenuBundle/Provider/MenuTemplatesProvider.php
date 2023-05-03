<?php

namespace Oro\Bundle\CommerceMenuBundle\Provider;

use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Collects and returns menu templates across all themes
 */
class MenuTemplatesProvider
{
    private const MENU_TEMPLATES_CACHE_KEY = 'oro_commerce_menu.provider.menu_templates_provider';

    private ThemeManager $themeManager;

    private CacheInterface $cache;

    public function __construct(ThemeManager $themeManager, CacheInterface $cache)
    {
        $this->themeManager = $themeManager;
        $this->cache = $cache;
    }

    public function getMenuTemplates(): array
    {
        return $this->cache->get(self::MENU_TEMPLATES_CACHE_KEY, function () {
            return $this->collectMenuTemplates();
        });
    }

    private function collectMenuTemplates(): array
    {
        $menuTemplates = [];

        foreach ($this->themeManager->getAllThemes() as $theme) {
            $themeConfig = $theme->getConfig();

            $menuTemplates[] = $themeConfig['menu_templates'] ?? [];
        }

        return array_merge(...$menuTemplates);
    }
}
