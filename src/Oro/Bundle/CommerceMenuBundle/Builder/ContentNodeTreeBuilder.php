<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;

/**
 * Menu builder that expands the content node tree as per "max_traverse_level" extra option
 * for the menu items with "content_node" extra option.
 * Works only for the backoffice requests.
 */
class ContentNodeTreeBuilder implements BuilderInterface
{
    private ManagerRegistry $managerRegistry;

    private LocalizationHelper $localizationHelper;

    private FrontendHelper $frontendHelper;

    public function __construct(
        ManagerRegistry $managerRegistry,
        LocalizationHelper $localizationHelper,
        FrontendHelper $frontendHelper
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->localizationHelper = $localizationHelper;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if ($this->frontendHelper->isFrontendRequest()) {
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

        if (!$menuItem->getLabel() || $menuItem->getLabel() === $menuItem->getName()) {
            $menuItem->setLabel($this->localizationHelper->getLocalizedValue($contentNode->getTitles()));
        }

        $maxTraverseLevel = (int)$menuItem->getExtra('max_traverse_level', 0);

        $contentNodes = $this->managerRegistry
            ->getRepository(ContentNode::class)
            ->getContentNodePlainTreeQueryBuilder($contentNode, $maxTraverseLevel)
            ->addSelect('titles')
            ->innerJoin('node.titles', 'titles')
            ->getQuery()
            ->getResult();

        $menuPrefix = $this->getMenuPrefix($menuItem, $contentNode->getId());
        $this->addChildren($menuPrefix, $menuItem, $contentNodes);
    }

    private function addChildren(
        string $menuPrefix,
        ItemInterface $menuItem,
        iterable $contentNodes
    ): void {
        $addedMenuItems = [];

        /** @var ContentNode $contentNode */
        foreach ($contentNodes as $contentNode) {
            $name = $menuPrefix . $contentNode->getId();
            $parentName = $menuPrefix . $contentNode->getParentNode()->getId();

            $parentMenuItem = $addedMenuItems[$parentName] ?? $menuItem;
            $maxTraverseLevel = (int)$parentMenuItem->getExtra('max_traverse_level', 0);

            $child = $parentMenuItem->addChild(
                $name,
                [
                    'label' => $this->localizationHelper->getLocalizedValue($contentNode->getTitles()),
                    'extras' => [
                        'content_node' => $contentNode,
                        'max_traverse_level' => $maxTraverseLevel - 1,
                    ],
                ]
            );

            $addedMenuItems[$name] = $child;
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
}
