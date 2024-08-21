<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\FrontendBundle\Provider\StorefrontIconsMappingProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Component\Layout\Exception\NotRequestContextRuntimeException;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;

/**
 * Replaces menu item icon with a corresponding storefront icon.
 */
class MenuIconsBuilder implements BuilderInterface
{
    private ?string $fallbackIcon = null;

    public function __construct(
        private StorefrontIconsMappingProvider $storefrontIconsMappingProvider,
        private CurrentThemeProvider $currentThemeProvider,
        private FrontendHelper $frontendHelper
    ) {
    }

    public function setFallbackIcon(?string $fallbackIcon): void
    {
        $this->fallbackIcon = $fallbackIcon;
    }

    /**
     * {@inheritDoc}
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return;
        }

        try {
            $currentThemeName = $this->currentThemeProvider->getCurrentThemeId();
        } catch (NotRequestContextRuntimeException $exception) {
            $currentThemeName = null;
        }

        if ($currentThemeName === null) {
            return;
        }

        $storefrontIconsMapping = $this->storefrontIconsMappingProvider
            ->getIconsMappingForTheme($currentThemeName);

        $this->applyRecursively($menu, $options, $storefrontIconsMapping);
    }

    private function applyRecursively(ItemInterface $menu, array $options, array $storefrontIconsMapping): void
    {
        $menuChildren = $menu->getChildren();
        foreach ($menuChildren as $menuChild) {
            $this->applyRecursively($menuChild, $options, $storefrontIconsMapping);
        }

        if ($menu->isDisplayed() !== false) {
            $icon = $menu->getExtra('icon');
            if ($icon !== null) {
                $menu->setExtra('icon', $storefrontIconsMapping[$icon] ?? $this->fallbackIcon);
            }
        }
    }
}
