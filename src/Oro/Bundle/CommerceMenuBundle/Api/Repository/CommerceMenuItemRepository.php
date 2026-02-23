<?php

namespace Oro\Bundle\CommerceMenuBundle\Api\Repository;

use Doctrine\Common\Collections\Criteria;
use Knp\Menu\ItemInterface;
use Oro\Bundle\ApiBundle\Util\ComparisonExpressionsVisitor;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\AttachmentBundle\Provider\FileUrlProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Api\Model\CommerceMenuItem;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Layout\DataProvider\MenuProvider;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The repository to get storefront menu items.
 */
class CommerceMenuItemRepository
{
    public function __construct(
        private readonly MenuProvider $menuProvider,
        private readonly TranslatorInterface $translator,
        private readonly AttachmentManager $attachmentManager
    ) {
    }

    public function getMenuItems(Criteria $criteria): array
    {
        $menuName = null;
        $depth = null;
        $visitor = new ComparisonExpressionsVisitor();
        $visitor->dispatch($criteria->getWhereExpression());
        $comparisons = $visitor->getComparisons();
        foreach ($comparisons as $comparison) {
            switch ($comparison->getField()) {
                case 'menu':
                    $menuName = $comparison->getValue()->getValue();
                    break;
                case 'depth':
                    $depth = $comparison->getValue()->getValue();
                    if (null !== $depth && $depth <= 0) {
                        // 0 or negative value means no limit
                        $depth = null;
                    }
                    break;
            }
        }
        if (null === $menuName) {
            throw new \LogicException('Undefined menu name.');
        }

        $rootItem = $this->menuProvider->getMenu($menuName);
        if (!$rootItem->hasChildren()) {
            return [];
        }

        $flattenedItems = MenuUpdateUtils::flattenMenuItem($rootItem);
        if (null !== $depth) {
            $flattenedItems = $this->filterByDepth($flattenedItems, $rootItem, $depth);
        }

        return $this->convertToMenuItemModels($flattenedItems, $rootItem);
    }

    /**
     * @param array<string, ItemInterface> $flattenedItems
     * @param ItemInterface                $rootItem
     * @param int                          $maxDepth
     *
     * @return array<string, ItemInterface>
     */
    private function filterByDepth(array $flattenedItems, ItemInterface $rootItem, int $maxDepth): array
    {
        $filtered = [];
        foreach ($flattenedItems as $name => $item) {
            $level = $this->getItemLevel($item, $rootItem);
            if ($level <= $maxDepth) {
                $filtered[$name] = $item;
            }
        }

        return $filtered;
    }

    /**
     * Gets the level of a menu item (1 = first level children of root, 2 = second level, etc.).
     */
    private function getItemLevel(ItemInterface $item, ItemInterface $rootItem): int
    {
        $level = 1;
        $current = $item;
        while ($current->getParent() !== null && $current->getParent() !== $rootItem) {
            $level++;
            $current = $current->getParent();
        }

        return $level;
    }

    /**
     * @param array<string, ItemInterface> $flattenedItems
     * @param ItemInterface                $rootItem
     *
     * @return CommerceMenuItem[]
     */
    private function convertToMenuItemModels(array $flattenedItems, ItemInterface $rootItem): array
    {
        $result = [];
        foreach ($flattenedItems as $item) {
            if ($item === $rootItem || $item->getExtra('isAllowed') === false || !$item->isDisplayed()) {
                continue;
            }

            $result[] = $this->createCommerceMenuItem($item, $rootItem);
        }

        return $result;
    }

    private function createCommerceMenuItem(ItemInterface $item, ItemInterface $rootItem): CommerceMenuItem
    {
        return new CommerceMenuItem(
            $item->getName(),
            $this->getLocalizedLabel($item),
            $item->getUri(),
            $this->getLocalizedDescription($item),
            [
                'position' => $item->getExtra('position'),
                'icon' => $item->getExtra('icon'),
                'image' => $this->getImageUrl($item->getExtra(MenuUpdate::IMAGE)),
                'screens' => $item->getExtra('screens'),
                'max_traverse_level' => $item->getExtra('max_traverse_level'),
                'menu_template' => $item->getExtra(MenuUpdate::MENU_TEMPLATE)
            ],
            $item->getLinkAttributes() ?? [],
            $this->getParentName($item, $rootItem),
            $item->getExtra('content_node')
        );
    }

    private function getLocalizedLabel(ItemInterface $item): string
    {
        $label = $item->getLabel();
        if ($item->getExtra(MenuUpdateInterface::IS_CUSTOM)) {
            return $label;
        }

        return $this->translator->trans($label);
    }

    private function getLocalizedDescription(ItemInterface $item): ?string
    {
        $description = $item->getExtra(MenuUpdateInterface::DESCRIPTION);
        if (!$description) {
            return null;
        }

        if ($item->getExtra(MenuUpdateInterface::IS_CUSTOM)) {
            return $description;
        }

        return $this->translator->trans($description);
    }

    private function getParentName(ItemInterface $item, ItemInterface $rootItem): ?string
    {
        $parentItem = $item->getParent();
        if (null === $parentItem || $parentItem === $rootItem) {
            return null;
        }

        return $parentItem->getName();
    }

    private function getImageUrl(mixed $image): ?string
    {
        if (!$image instanceof File) {
            return null;
        }

        return $this->attachmentManager->getFileUrl(
            $image,
            FileUrlProviderInterface::FILE_ACTION_GET,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
