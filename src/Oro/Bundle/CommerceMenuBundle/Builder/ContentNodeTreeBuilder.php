<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
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

        $maxTraverseLevel = (int)$menuItem->getExtra('max_traverse_level', 0);
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

        $menuPrefix = $this->getMenuPrefix($menuItem, $contentNode->getId());

        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(ContentNode::class);

        $this->addChildren($entityManager, $menuPrefix, $menuItem, $resolvedNode->getChildNodes());
    }

    /**
     * @param EntityManager $entityManager
     * @param string $menuPrefix
     * @param ItemInterface $menuItem
     * @param iterable<ResolvedContentNode> $resolvedContentNodes
     */
    private function addChildren(
        EntityManager $entityManager,
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
                        'content_node' => $entityManager->getReference(ContentNode::class, $resolvedNode->getId()),
                        'max_traverse_level' => $maxTraverseLevel - 1,
                        // Max traverse level option should be disabled for synthetic menu items.
                        'max_traverse_level_disabled' => true,
                        'translate_disabled' => true,
                    ],
                ]
            );

            $this->addChildren($entityManager, $menuPrefix, $child, $resolvedNode->getChildNodes());
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
