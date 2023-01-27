<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\DependencyInjection\Configuration;
use Oro\Bundle\ConfigBundle\Config\ConfigManager as SystemConfigManager;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\NavigationBundle\Provider\MenuUpdateProvider;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Adds navigation root menu items to the configured main navigation menu from either web catalog or master catalog
 * depending on whether web catalog is enabled or not.
 */
class NavigationRootBuilder implements BuilderInterface
{
    private WebCatalogProvider $webCatalogProvider;

    private BuilderInterface $masterCatalogNavigationRootBuilder;

    private BuilderInterface $webCatalogNavigationRootBuilder;

    private SystemConfigManager $systemConfigManager;

    public function __construct(
        WebCatalogProvider $webCatalogProvider,
        BuilderInterface $masterCatalogNavigationRootBuilder,
        BuilderInterface $webCatalogNavigationRootBuilder,
        SystemConfigManager $systemConfigManager
    ) {
        $this->webCatalogProvider = $webCatalogProvider;
        $this->masterCatalogNavigationRootBuilder = $masterCatalogNavigationRootBuilder;
        $this->webCatalogNavigationRootBuilder = $webCatalogNavigationRootBuilder;
        $this->systemConfigManager = $systemConfigManager;
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if (!$menu->isDisplayed()) {
            return;
        }

        $mainNavigationMenuName = $this->systemConfigManager
            ->get(Configuration::getConfigKeyByName(Configuration::MAIN_NAVIGATION_MENU));
        if ($menu->getName() !== $mainNavigationMenuName) {
            return;
        }

        $options['website'] = $this->getWebsite($options);
        if ($this->webCatalogProvider->getWebCatalog($options['website'])) {
            $this->webCatalogNavigationRootBuilder->build($menu, $options, $alias);
        } else {
            $this->masterCatalogNavigationRootBuilder->build($menu, $options, $alias);
        }
    }

    public function getWebsite(array $options): ?Website
    {
        $website = null;
        if (isset($options[MenuUpdateProvider::SCOPE_CONTEXT_OPTION])) {
            $scopeContext = $options[MenuUpdateProvider::SCOPE_CONTEXT_OPTION];
            if ($scopeContext instanceof Scope) {
                $website = $scopeContext->getWebsite();
            } elseif (isset($scopeContext['website']) && $scopeContext['website'] instanceof Website) {
                $website = $scopeContext['website'];
            }
        }

        return $website;
    }
}
