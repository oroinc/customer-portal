<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Menu\Frontend;

use Knp\Menu\ItemInterface;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

/**
 * Builds Quick Access Button menu items based on storefront menu configuration.
 */
class QuickAccessButtonFrontendMenuMenuBuilder implements BuilderInterface
{
    private BuilderInterface $menuBuilder;
    private int $maxNestingLevel = 1;

    public function __construct(
        BuilderInterface $menuBuilder,
    ) {
        $this->menuBuilder = $menuBuilder;
    }

    public function setMaxNestingLevel(int $maxNestingLevel): void
    {
        $this->maxNestingLevel = max(1, $maxNestingLevel);
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if ('quick_access_button_menu' !== $alias) {
            return;
        }

        /** @var QuickAccessButtonConfig|null $configValue */
        $configValue = $options['qab_config'] ?? null;

        if (QuickAccessButtonConfig::TYPE_MENU !== $configValue?->getType()) {
            return;
        }

        $menuName = $configValue->getMenu();
        if ('quick_access_button_menu' === $menuName) {
            $menu->setExtra(QuickAccessButtonConfig::MENU_NOT_RESOLVED, true);

            return;
        }
        $this->menuBuilder->build(
            $menu,
            array_merge($options, ['check_access_not_logged_in' => true]),
            $configValue->getMenu()
        );

        $this->updateMaxNestingLevel($menu);

        if (count($menu->getChildren()) === 0 && null == $menu->getUri()) {
            $menu->setExtra(QuickAccessButtonConfig::MENU_NOT_RESOLVED, true);

            return;
        }
    }

    /**
     * Updates menu levels according to max expected nesting levels
     */
    private function updateMaxNestingLevel(ItemInterface $item): void
    {
        if ($item->getLevel() > $this->maxNestingLevel) {
            $item->setDisplayChildren(false);
            $item->setDisplay(false);

            return;
        }

        if ($item->getLevel() == $this->maxNestingLevel) {
            $item->setDisplayChildren(false);
        }

        if (!$item->hasChildren()) {
            return;
        }

        foreach ($item->getChildren() as $child) {
            $this->updateMaxNestingLevel($child);
        }
    }
}
