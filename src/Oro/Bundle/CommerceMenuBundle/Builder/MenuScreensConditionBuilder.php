<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\FrontendBundle\Provider\ScreensProviderInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

class MenuScreensConditionBuilder implements BuilderInterface
{
    /**
     * @var ScreensProviderInterface
     */
    private $screensProvider;

    public function __construct(ScreensProviderInterface $screensProvider)
    {
        $this->screensProvider = $screensProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null)
    {
        $this->applyScreensRecursively($menu, $options);
    }

    private function applyScreensRecursively(ItemInterface $menu, array $options)
    {
        $menuChildren = $menu->getChildren();

        foreach ($menuChildren as $menuChild) {
            $this->applyScreensRecursively($menuChild, $options);
        }

        if ($menu->isDisplayed() !== false) {
            $menuItemScreenNames = $this->fetchScreenNamesFromMenuItem($menu);
            $classes = $this->getClassesFromScreenNames($menuItemScreenNames);

            $this->addClassesToMenuItem($menu, $classes);
        }
    }

    /**
     * @param array $screenNames
     *
     * @return array
     */
    private function getClassesFromScreenNames(array $screenNames)
    {
        $classes = [];
        foreach ($screenNames as $screenName) {
            $screenInfo = $this->screensProvider->getScreen($screenName);
            if (!$screenInfo) {
                continue;
            }

            $classes[] = $screenInfo['hidingCssClass'];
        }

        return $classes;
    }

    private function addClassesToMenuItem(ItemInterface $menu, array $newClasses)
    {
        if (!$newClasses) {
            return;
        }

        // Fetch classes from attributes.
        $classes = explode(' ', (string)$menu->getAttribute('class', ''));
        // Add new classes.
        $classes = array_merge($classes, $newClasses);
        // Exclude empty and repeating classes.
        $classes = array_unique(array_filter($classes));

        $menu->setAttribute('class', implode(' ', $classes));
    }

    /**
     * @param ItemInterface $menuItem
     *
     * @return array
     */
    private function fetchScreenNamesFromMenuItem(ItemInterface $menuItem)
    {
        $extras = $menuItem->getExtras();
        if (array_key_exists('screens', $extras)) {
            return (array)$extras['screens'];
        }

        return [];
    }
}
