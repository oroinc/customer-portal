<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Component\Website\WebsiteInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Sets for the menu root the "content_node" extra option to Web Catalog navigation root content node.
 */
class WebCatalogNavigationRootBuilder implements BuilderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private WebCatalogProvider $webCatalogProvider;

    private MenuTemplatesProvider $menuTemplatesProvider;

    private array $treeItemOptions = [];

    public function __construct(WebCatalogProvider $webCatalogProvider, MenuTemplatesProvider $menuTemplatesProvider)
    {
        $this->webCatalogProvider = $webCatalogProvider;
        $this->menuTemplatesProvider = $menuTemplatesProvider;

        $this->logger = new NullLogger();
    }

    /**
     * Options to pass to the content node tree items.
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

        /** @var WebsiteInterface|null $website */
        $website = $options['website'] ?? null;
        if ($website && !$website instanceof WebsiteInterface) {
            $this->logger->error(
                'Option "website" with value {actual_type} is expected to be {expected_type}',
                ['actual_type' => get_debug_type($website), 'expected_type' => WebsiteInterface::class]
            );
            return;
        }

        $rootContentNode = $this->webCatalogProvider->getNavigationRootWithCatalogRootFallback($website);
        if (!$rootContentNode) {
            // Web catalog is not specified for current scope.
            return;
        }

        $menu->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $rootContentNode);

        if ($menu->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL) === null) {
            $menu->setExtra(
                MenuUpdate::MAX_TRAVERSE_LEVEL,
                max(0, $menu->getExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 0))
            );
        }

        $treeItemOptions = $this->treeItemOptions;
        $treeItemOptions['extras'][MenuUpdate::MENU_TEMPLATE] = $this->getFirstAvailableMenuTemplate();
        $menu->setExtra(ContentNodeTreeBuilder::TREE_ITEM_OPTIONS, $treeItemOptions);
    }

    private function getFirstAvailableMenuTemplate(): string
    {
        $menuTemplateNames = array_keys($this->menuTemplatesProvider->getMenuTemplates());

        return reset($menuTemplateNames) ?: '';
    }
}
