<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CatalogBundle\Provider\MasterCatalogRootProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;

/**
 * Sets for the menu root the "category" extra option to Master Catalog root category.
 */
class MasterCatalogNavigationRootBuilder implements BuilderInterface
{
    private MasterCatalogRootProviderInterface $masterCatalogRootProvider;

    private MenuTemplatesProvider $menuTemplatesProvider;

    private array $treeItemOptions = [];

    public function __construct(
        MasterCatalogRootProviderInterface $masterCatalogRootProvider,
        MenuTemplatesProvider $menuTemplatesProvider
    ) {
        $this->masterCatalogRootProvider = $masterCatalogRootProvider;
        $this->menuTemplatesProvider = $menuTemplatesProvider;
    }

    /**
     * Options to pass to the category tree items.
     */
    public function setTreeItemOptions(array $treeItemOptions): void
    {
        $this->treeItemOptions = $treeItemOptions;
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if (!$menu->isDisplayed()) {
            return;
        }

        $rootCategory = $this->masterCatalogRootProvider->getMasterCatalogRoot();

        $menu->setExtra(MenuUpdate::TARGET_CATEGORY, $rootCategory);

        if ($menu->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL) === null) {
            $menu->setExtra(
                MenuUpdate::MAX_TRAVERSE_LEVEL,
                max(0, $menu->getExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 0))
            );
        }

        $treeItemOptions = $this->treeItemOptions;
        $treeItemOptions['extras'][MenuUpdate::MENU_TEMPLATE] = $this->getFirstAvailableMenuTemplate();
        $menu->setExtra(CategoryTreeBuilder::TREE_ITEM_OPTIONS, $treeItemOptions);
    }

    private function getFirstAvailableMenuTemplate(): string
    {
        $menuTemplateNames = array_keys($this->menuTemplatesProvider->getMenuTemplates());

        return reset($menuTemplateNames) ?: '';
    }
}
