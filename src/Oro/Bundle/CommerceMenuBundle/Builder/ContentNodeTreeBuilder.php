<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\LocaleBundle\Tools\LocalizedFallbackValueHelper;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Event\MenuUpdatesApplyAfterEvent;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Oro\Bundle\NavigationBundle\MenuUpdate\Applier\Model\MenuUpdateApplierContext;
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
    public const IS_TREE_ITEM = 'content_node_tree_item';
    public const TREE_ITEM_OPTIONS = 'content_node_tree_item_options';

    private ManagerRegistry $managerRegistry;

    private MenuContentNodesProviderInterface $menuContentNodesProvider;

    private LocalizationHelper $localizationHelper;

    /**
     * @var array<string,MenuUpdateApplierContext> Contexts indexed by menu name.
     */
    private array $menuUpdateApplierContexts = [];

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
        $menuItemsByName = MenuUpdateUtils::flattenMenuItem($menu);
        $maxNestingLevel = max(0, (int)$menu->getExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 0));

        $this->applyRecursively(
            $menu,
            $menuItemsByName,
            $options,
            $maxNestingLevel,
            $this->menuUpdateApplierContexts[$menu->getName()] ?? null
        );
    }

    private function applyRecursively(
        ItemInterface $menuItem,
        array &$menuItemsByName,
        array $menuOptions,
        int $maxNestingLevel,
        ?MenuUpdateApplierContext $menuUpdateApplierContext
    ): void {
        foreach ($menuItem->getChildren() as $menuChild) {
            $this->applyRecursively(
                $menuChild,
                $menuItemsByName,
                $menuOptions,
                $maxNestingLevel,
                $menuUpdateApplierContext
            );
        }

        /** @var ContentNode|null $contentNode */
        $contentNode = $menuItem->getExtra(MenuUpdate::TARGET_CONTENT_NODE);
        if (!$contentNode instanceof ContentNode) {
            return;
        }

        if ($menuItem->getExtra(self::IS_TREE_ITEM) || $menuUpdateApplierContext?->isLostItem($menuItem->getName())) {
            return;
        }

        $maxTraverseLevel = max(0, (int)$menuItem->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 0));
        if ($maxNestingLevel > 0) {
            $allowedTraverseLevel = max(0, $maxNestingLevel - $menuItem->getLevel());
            $maxTraverseLevel = min($allowedTraverseLevel, $maxTraverseLevel);
        }

        $resolvedNode = $this->menuContentNodesProvider->getResolvedContentNode(
            $contentNode,
            ['tree_depth' => $maxTraverseLevel]
        );
        if (!$resolvedNode) {
            $menuItem->setDisplay(false);
            return;
        }

        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(ContentNode::class);

        // Explicit passing of localization avoids further unnecessary calls to getCurrentLocalization.
        $localization = $this->localizationHelper->getCurrentLocalization();

        if (!$menuItem->isRoot()) {
            $this->setMenuItemData($menuItem, $resolvedNode, $entityManager, $localization);
        }

        $this->setAllowedTraverseLevel($menuItem, $maxTraverseLevel);

        $treeItemNamePrefix = self::getTreeItemNamePrefix($menuItem, $contentNode->getId());

        $this->addTreeItems(
            $menuItem,
            $resolvedNode->getChildNodes(),
            $entityManager,
            $localization,
            $treeItemNamePrefix,
            $menuItemsByName,
            $menuOptions,
            $menuUpdateApplierContext
        );
    }

    /**
     * @param ItemInterface $parentMenuItem
     * @param iterable<ResolvedContentNode> $resolvedContentNodes
     * @param EntityManager $entityManager
     * @param Localization|null $localization
     * @param string $treeItemNamePrefix
     * @param array<string,ItemInterface> $menuItemsByName
     * @param array $menuOptions
     * @param MenuUpdateApplierContext|null $menuUpdateApplierContext
     */
    private function addTreeItems(
        ItemInterface $parentMenuItem,
        iterable $resolvedContentNodes,
        EntityManager $entityManager,
        ?Localization $localization,
        string $treeItemNamePrefix,
        array &$menuItemsByName,
        array $menuOptions,
        ?MenuUpdateApplierContext $menuUpdateApplierContext
    ): void {
        $index = 0;
        $parentMaxTraverseLevel = $parentMenuItem->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 0);
        if ($parentMaxTraverseLevel <= 0) {
            return;
        }

        $options = array_merge_recursive($menuOptions, $parentMenuItem->getExtra(self::TREE_ITEM_OPTIONS, []));
        $options['extras'][self::IS_TREE_ITEM] = true;

        $parentMaxTraverseLevel = max(0, $parentMaxTraverseLevel - 1);

        foreach ($resolvedContentNodes as $resolvedNode) {
            $name = $treeItemNamePrefix . $resolvedNode->getId();

            if (isset($menuItemsByName[$name])) {
                if ($menuItemsByName[$name]->getExtra(MenuUpdateInterface::IS_SYNTHETIC)) {
                    continue;
                }

                $menuUpdateApplierContext?->removeLostItem($name);
                $menuItemsByName[$name]->setExtra(self::IS_TREE_ITEM, true);
            } else {
                $options['extras'][MenuUpdateInterface::POSITION] = $index;
                $menuItemsByName[$name] = $parentMenuItem->addChild($name, $options);
            }

            $index++;

            $this->setMenuItemData(
                $menuItemsByName[$name],
                $resolvedNode,
                $entityManager,
                $localization
            );

            $this->setAllowedTraverseLevel($menuItemsByName[$name], $parentMaxTraverseLevel);

            $this->addTreeItems(
                $menuItemsByName[$name],
                $resolvedNode->getChildNodes(),
                $entityManager,
                $localization,
                $treeItemNamePrefix,
                $menuItemsByName,
                $menuOptions,
                $menuUpdateApplierContext
            );
        }
    }

    private function setMenuItemData(
        ItemInterface $menuItem,
        ResolvedContentNode $resolvedNode,
        EntityManager $entityManager,
        ?Localization $localization
    ): void {
        $menuItem->setUri($this->getUri($resolvedNode, $localization));

        if ($menuItem->getLabel() === $menuItem->getName()) {
            $menuItem->setLabel($this->getLabel($resolvedNode, $localization));
        }

        $menuItem->setExtra(
            MenuUpdateInterface::TITLES,
            LocalizedFallbackValueHelper::cloneCollection($resolvedNode->getTitles())
        );

        $menuItem->setExtra(
            MenuUpdate::TARGET_CONTENT_NODE,
            $entityManager->getReference(ContentNode::class, $resolvedNode->getId())
        );

        $menuItem->setExtra(MenuUpdateInterface::IS_TRANSLATE_DISABLED, true);
    }

    private function setAllowedTraverseLevel(ItemInterface $menuItem, int $allowedTraverseLevel): void
    {
        $maxTraverseLevel = $menuItem->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL);
        if ($maxTraverseLevel !== null) {
            $maxTraverseLevel = min($maxTraverseLevel, $allowedTraverseLevel);
        } else {
            $maxTraverseLevel = $allowedTraverseLevel;
        }

        $menuItem->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, $maxTraverseLevel);
    }

    private function getLabel(ResolvedContentNode $resolvedNode, ?Localization $localization): string
    {
        return (string)$this->localizationHelper->getLocalizedValue($resolvedNode->getTitles(), $localization);
    }

    private function getUri(ResolvedContentNode $resolvedNode, ?Localization $localization): string
    {
        return (string)$this->localizationHelper
            ->getLocalizedValue($resolvedNode->getResolvedContentVariant()->getLocalizedUrls(), $localization);
    }

    public static function getTreeItemNamePrefix(ItemInterface $menuItem, int $contentNodeId): string
    {
        $itemName = $menuItem->getName();
        $idPosition = strrpos($itemName, '__' . $contentNodeId);
        if ($idPosition !== false) {
            return substr($itemName, 0, $idPosition) . '__';
        }

        return 'menu_item_' . sha1('content_node_' . $itemName) . '__';
    }

    public function onMenuUpdatesApplyAfter(MenuUpdatesApplyAfterEvent $event): void
    {
        $menuUpdateApplierContext = $event->getContext();
        $this->menuUpdateApplierContexts[$menuUpdateApplierContext->getMenu()->getName()] = $menuUpdateApplierContext;
    }
}
