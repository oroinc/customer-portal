<?php

namespace Oro\Bundle\CommerceMenuBundle\Provider;

use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Collects and returns menu templates across all themes
 */
class MenuTemplatesProvider
{
    private ThemeManager $themeManager;
    private CacheInterface $cache;

    public function __construct(ThemeManager $themeManager, CacheInterface $cache)
    {
        $this->themeManager = $themeManager;
        $this->cache = $cache;
    }

    public function getMenuTemplates(): array
    {
        return $this->cache->get('commerce_menu_templates', function () {
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
