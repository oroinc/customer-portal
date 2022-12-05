<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\ContentNodeUtils\ContentNodeTreeResolverInterface;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Provider\RequestWebContentScopeProvider;

/**
 * Menu builder that adds URI for menu items with "content_node" extra option.
 * Expands the content node tree as per "max_traverse_level" extra option.
 * Works only for the storefront requests.
 */
class ResolvedContentNodeTreeBuilder implements BuilderInterface
{
    private RequestWebContentScopeProvider $requestWebContentScopeProvider;

    private ContentNodeTreeResolverInterface $contentNodeTreeResolver;

    private LocalizationHelper $localizationHelper;

    private FrontendHelper $frontendHelper;

    public function __construct(
        RequestWebContentScopeProvider $requestWebContentScopeProvider,
        ContentNodeTreeResolverInterface $contentNodeTreeResolver,
        LocalizationHelper $localizationHelper,
        FrontendHelper $frontendHelper
    ) {
        $this->requestWebContentScopeProvider = $requestWebContentScopeProvider;
        $this->contentNodeTreeResolver = $contentNodeTreeResolver;
        $this->localizationHelper = $localizationHelper;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return;
        }

        $this->applyRecursively($menu);
    }

    private function applyRecursively(ItemInterface $menuItem): void
    {
        if (!$menuItem->isDisplayed()) {
            return;
        }

        foreach ($menuItem->getChildren() as $menuChild) {
            $this->applyRecursively($menuChild);
        }

        /** @var ContentNode|null $contentNode */
        $contentNode = $menuItem->getExtra(MenuUpdate::TARGET_CONTENT_NODE);
        if (!$contentNode) {
            return;
        }

        $scopes = (array)$this->requestWebContentScopeProvider->getScopes();
        if (!$scopes) {
            $menuItem->setDisplay(false);
            return;
        }

        $maxTraverseLevel = (int)$menuItem->getExtra('max_traverse_level', 0);

        $resolvedNode = $this->contentNodeTreeResolver
            ->getResolvedContentNode($contentNode, $scopes, ['tree_depth' => $maxTraverseLevel]);
        if (!$resolvedNode) {
            $menuItem->setDisplay(false);
            return;
        }

        $menuItem->setUri($this->getUri($resolvedNode));

        if (!$menuItem->getLabel() || $menuItem->getLabel() === $menuItem->getName()) {
            $menuItem->setLabel($this->getLabel($resolvedNode));
        }

        $menuPrefix = $this->getMenuPrefix($menuItem, $contentNode->getId());
        $this->addChildren($menuPrefix, $menuItem, $resolvedNode->getChildNodes());
    }

    /**
     * @param string $menuPrefix
     * @param ItemInterface $menuItem
     * @param iterable<ResolvedContentNode> $resolvedContentNodes
     */
    private function addChildren(
        string $menuPrefix,
        ItemInterface $menuItem,
        iterable $resolvedContentNodes
    ): void {
        $maxTraverseLevel = (int)$menuItem->getExtra('max_traverse_level', 0);

        foreach ($resolvedContentNodes as $resolvedNode) {
            $name = $menuPrefix . $resolvedNode->getId();
            $child = $menuItem->addChild(
                $name,
                [
                    'label' => $this->getLabel($resolvedNode),
                    'uri' => $this->getUri($resolvedNode),
                    'extras' => [
                        'isAllowed' => true,
                        'content_node' => $resolvedNode,
                        'max_traverse_level' => $maxTraverseLevel - 1,
                    ],
                ]
            );

            $this->addChildren($menuPrefix, $child, $resolvedNode->getChildNodes());
        }
    }

    private function getMenuPrefix(ItemInterface $menuItem, int $contentNodeId): string
    {
        $menuPrefix = 'content_node_' . $contentNodeId;
        if ($menuItem->getName() !== $menuPrefix) {
            $menuPrefix = $menuItem->getName();
        }

        return $menuPrefix . '_';
    }

    private function getLabel(ResolvedContentNode $resolvedNode): string
    {
        return (string)$this->localizationHelper->getLocalizedValue($resolvedNode->getTitles());
    }

    private function getUri(ResolvedContentNode $resolvedNode): string
    {
        return (string)$this->localizationHelper
            ->getLocalizedValue($resolvedNode->getResolvedContentVariant()->getLocalizedUrls());
    }
}
