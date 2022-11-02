<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Provider\RequestWebContentScopeProvider;

/**
 * Menu builder which sets URI for menu items with content node as target.
 */
class ContentNodeTargetBuilder implements BuilderInterface
{
    /** @var RequestWebContentScopeProvider */
    private $requestWebContentScopeProvider;

    /** @var LocalizationHelper */
    private $localizationHelper;

    /** @var ScopeManager */
    private $scopeManager;

    public function __construct(
        RequestWebContentScopeProvider $requestWebContentScopeProvider,
        ScopeManager $scopeManager,
        LocalizationHelper $localizationHelper
    ) {
        $this->requestWebContentScopeProvider = $requestWebContentScopeProvider;
        $this->localizationHelper = $localizationHelper;
        $this->scopeManager = $scopeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        $this->applyRecursively($menu, $options);
    }

    private function applyRecursively(ItemInterface $menuItem, array $options): void
    {
        if (!$menuItem->isDisplayed()) {
            return;
        }

        foreach ($menuItem->getChildren() as $menuChild) {
            $this->applyRecursively($menuChild, $options);
        }

        /** @var ContentNode|null $contentNode */
        $contentNode = $menuItem->getExtra('content_node');
        if (!$contentNode) {
            return;
        }

        if ($this->isScopeMatches($contentNode)) {
            $url = $this->localizationHelper->getLocalizedValue($contentNode->getLocalizedUrls());
            $menuItem->setUri($url);
        } else {
            $menuItem->setDisplay(false);
        }
    }

    private function isScopeMatches(ContentNode $contentNode): bool
    {
        $contentNodeScopes = $contentNode->getScopesConsideringParent();
        if ($contentNodeScopes->count() === 0) {
            return true;
        }

        $scopeMatched = false;
        $currentScopeCriteria = $this->requestWebContentScopeProvider->getScopeCriteria();
        if ($currentScopeCriteria) {
            foreach ($contentNodeScopes as $scope) {
                if ($this->scopeManager->isScopeMatchCriteria($scope, $currentScopeCriteria, 'web_content')) {
                    $scopeMatched = true;
                }
            }
        }

        return $scopeMatched;
    }
}
