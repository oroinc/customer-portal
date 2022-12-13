<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\NavigationBundle\Provider\MenuUpdateProvider;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Adds navigation root menu items from either web catalog or master catalog depending on whether web catalog
 * is enabled or not.
 */
class NavigationRootBuilder implements BuilderInterface
{
    private WebCatalogProvider $webCatalogProvider;

    private BuilderInterface $masterCatalogNavigationRootBuilder;

    private BuilderInterface $webCatalogNavigationRootBuilder;

    private string $targetMenuName = '';

    public function __construct(
        WebCatalogProvider $webCatalogProvider,
        BuilderInterface $masterCatalogNavigationRootBuilder,
        BuilderInterface $webCatalogNavigationRootBuilder
    ) {
        $this->webCatalogProvider = $webCatalogProvider;
        $this->masterCatalogNavigationRootBuilder = $masterCatalogNavigationRootBuilder;
        $this->webCatalogNavigationRootBuilder = $webCatalogNavigationRootBuilder;
    }

    /**
     * The name of menu to which the navigation root items should be added.
     */
    public function setTargetMenuName(string $targetMenuName): void
    {
        $this->targetMenuName = $targetMenuName;
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if (!$menu->isDisplayed()) {
            return;
        }

        if ($menu->getName() !== $this->targetMenuName) {
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
