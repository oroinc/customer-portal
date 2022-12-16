<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\NavigationBundle\MenuUpdateApplier\MenuUpdateApplier;
use Oro\Bundle\NavigationBundle\Utils\LostItemsManipulator;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Menu\MenuContentNodesProviderInterface;

/**
 * Menu builder that expands the content node tree as per "max_traverse_level" extra option
 * for the menu items with "content_node" extra option.
 */
class ContentNodeTreeBuilder implements BuilderInterface
{
    private ManagerRegistry $managerRegistry;

    private MenuContentNodesProviderInterface $menuContentNodesProvider;

    private LocalizationHelper $localizationHelper;

    public function __construct(
        ManagerRegistry $managerRegistry,
        MenuContentNodesProviderInterface $menuContentNodesProvider,
        LocalizationHelper $localizationHelper
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->menuContentNodesProvider = $menuContentNodesProvider;
        $this->localizationHelper = $localizationHelper;
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
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
        if (!$contentNode instanceof ContentNode) {
            return;
        }

        $maxTraverseLevel = max(
            0,
            min(
                (int)$menuItem->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 0),
                MenuUpdateUtils::getAllowedNestingLevel($menuItem)
            )
        );
        $menuItem->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, $maxTraverseLevel);

        $resolvedNode = $this->menuContentNodesProvider->getResolvedContentNode(
            $contentNode,
            ['tree_depth' => $maxTraverseLevel]
        );
        if (!$resolvedNode) {
            $menuItem->setDisplay(false);
            return;
        }

        $menuItem->setUri($this->getUri($resolvedNode));

        if (!$menuItem->getLabel() || $menuItem->getLabel() === $menuItem->getName()) {
            $menuItem->setLabel($this->localizationHelper->getLocalizedValue($resolvedNode->getTitles()));
        }

        $lostItems = LostItemsManipulator::getLostItemsContainer($menuItem, false)?->getChildren() ?? [];
        $prefixForChildren = $this->getPrefixForChildren($menuItem, $contentNode->getId(), $lostItems);

        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(ContentNode::class);

        $this->addChildren(
            $entityManager,
            $menuItem,
            $resolvedNode->getChildNodes(),
            $lostItems,
            $prefixForChildren,
            $maxTraverseLevel
        );
    }

    /**
     * @param EntityManager $entityManager
     * @param ItemInterface $menuItem
     * @param iterable<ResolvedContentNode> $resolvedContentNodes
     * @param array<ItemInterface> $lostItems
     * @param string $prefixForChildren
     * @param int $maxTraverseLevel
     */
    private function addChildren(
        EntityManager $entityManager,
        ItemInterface $menuItem,
        iterable $resolvedContentNodes,
        array $lostItems,
        string $prefixForChildren,
        int $maxTraverseLevel
    ): void {
        foreach ($resolvedContentNodes as $resolvedNode) {
            $name = $prefixForChildren . $resolvedNode->getId();
            $child = $lostItems[$name] ?? null;
            if ($child) {
                // If the child is already created in the lost items container, mark it as custom to make it move
                // to its implied parent menu item.
                $child->setExtra(MenuUpdateApplier::IS_CUSTOM, true);
                continue;
            }

            $child = $menuItem->addChild(
                $name,
                [
                    'label' => $this->getLabel($resolvedNode),
                    'uri' => $this->getUri($resolvedNode),
                    'extras' => [
                        'isAllowed' => true,
                        'translate_disabled' => true,
                        MenuUpdate::TARGET_CONTENT_NODE => $entityManager
                            ->getReference(ContentNode::class, $resolvedNode->getId()),
                        MenuUpdate::MAX_TRAVERSE_LEVEL => max(0, $maxTraverseLevel - 1),
                    ],
                ]
            );

            $this->addChildren(
                $entityManager,
                $child,
                $resolvedNode->getChildNodes(),
                $lostItems,
                $prefixForChildren,
                $maxTraverseLevel - 1
            );
        }
    }

    private function getPrefixForChildren(ItemInterface $menuItem, int $contentNodeId, array $lostItems): string
    {
        $itemName = $menuItem->getName();
        if (isset($lostItems[$itemName])) {
            // Ensures that
            return substr($itemName, 0, strrpos($itemName, '_' . $contentNodeId)) . '_';
        }

        return $itemName . '_';
    }

    public function getLabel(ResolvedContentNode $resolvedNode): string
    {
        return (string)$this->localizationHelper->getLocalizedValue($resolvedNode->getTitles());
    }

    public function getUri(ResolvedContentNode $resolvedNode): string
    {
        return (string)$this->localizationHelper
            ->getLocalizedValue($resolvedNode->getResolvedContentVariant()->getLocalizedUrls());
    }
}
