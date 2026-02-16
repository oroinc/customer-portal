<?php

namespace Oro\Bundle\CommerceMenuBundle\Api\Processor\Menu;

use Knp\Menu\ItemInterface;
use Oro\Bundle\ApiBundle\Processor\GetList\GetListContext;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\AttachmentBundle\Provider\FileUrlProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Api\Model\CommerceMenuItem;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Layout\DataProvider\MenuProvider;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Loads menu data as a flat list with parent relationships.
 */
class LoadMenuProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MenuProvider $menuProvider,
        private readonly TranslatorInterface $translator,
        private readonly AttachmentManager $attachmentManager,
        private readonly string $defaultMenuName = 'frontend_menu'
    ) {
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var GetListContext $context */

        if ($context->hasResult()) {
            return;
        }

        $menuName = $this->getMenuName($context) ?? $this->defaultMenuName;

        $depth = $this->getDepth($context);

        $rootMenuItem = $this->menuProvider->getMenu($menuName);

        if (!$rootMenuItem->hasChildren()) {
            $context->setResult([]);
            return;
        }

        $flattenedItems = MenuUpdateUtils::flattenMenuItem($rootMenuItem);

        if ($depth > 0) {
            $flattenedItems = $this->filterByDepth($flattenedItems, $rootMenuItem, $depth);
        }

        $menuItems = $this->convertToMenuItemModels($flattenedItems, $rootMenuItem);

        $context->setResult($menuItems);
    }

    private function getMenuName(GetListContext $context): ?string
    {
        $filterValues = $context->getFilterValues();
        $menuFilterValue = $filterValues->getOne('menu');

        if ($menuFilterValue === null) {
            return null;
        }

        $menuName = $menuFilterValue->getValue();
        if (!$menuName) {
            return null;
        }

        return (string)$menuName;
    }

    /**
     * Gets normalized depth value from context.
     * Returns -1 if depth is not provided, 0, or negative (no limit).
     * Returns positive integer if depth is provided and > 0.
     */
    private function getDepth(GetListContext $context): int
    {
        $filterValues = $context->getFilterValues();
        $depthFilterValue = $filterValues->getOne('depth');

        if ($depthFilterValue === null) {
            return -1;
        }

        $depth = (int)$depthFilterValue->getValue();

        return $depth <= 0 ? -1 : $depth;
    }

    /**
     * Filters menu items by depth level.
     *
     * @param array<string, ItemInterface> $flattenedItems
     * @param ItemInterface $rootMenuItem
     * @param int $maxDepth Maximum depth level (1 = root + 1 level, 2 = root + 2 levels, etc.)
     * @return array<string, ItemInterface>
     */
    private function filterByDepth(array $flattenedItems, ItemInterface $rootMenuItem, int $maxDepth): array
    {
        $filtered = [];

        foreach ($flattenedItems as $name => $item) {
            $level = $this->getItemLevel($item, $rootMenuItem);
            if ($level <= $maxDepth) {
                $filtered[$name] = $item;
            }
        }

        return $filtered;
    }

    /**
     * Gets the level of a menu item (1 = first level children of root, 2 = second level, etc.).
     */
    private function getItemLevel(ItemInterface $item, ItemInterface $rootMenuItem): int
    {
        $level = 1;
        $current = $item;

        while ($current->getParent() !== null && $current->getParent() !== $rootMenuItem) {
            $level++;
            $current = $current->getParent();
        }

        return $level;
    }

    /**
     * @param array<string, ItemInterface> $flattenedItems
     * @param ItemInterface $rootMenuItem
     * @return CommerceMenuItem[]
     */
    private function convertToMenuItemModels(array $flattenedItems, ItemInterface $rootMenuItem): array
    {
        $result = [];

        foreach ($flattenedItems as $item) {
            if ($item === $rootMenuItem || $item->getExtra('isAllowed') === false || !$item->isDisplayed()) {
                continue;
            }

            $parentItem = $item->getParent();
            $parentName = null;

            if ($parentItem && $parentItem !== $rootMenuItem) {
                $parentName = $parentItem->getName();
            }

            $label = $this->getLocalizedLabel($item);

            $result[] = new CommerceMenuItem(
                name: $item->getName(),
                label: $label,
                uri: $item->getUri(),
                description: $this->getLocalizedDescription($item),
                extras: [
                    'position' => $item->getExtra('position'),
                    'icon' => $item->getExtra('icon'),
                    'image' => $this->getImageUrl($item->getExtra(MenuUpdate::IMAGE)),
                    'screens' => $item->getExtra('screens'),
                    'max_traverse_level' => $item->getExtra('max_traverse_level'),
                    'menu_template' => $item->getExtra(MenuUpdate::MENU_TEMPLATE),
                ],
                link_attributes: $item->getLinkAttributes() ?? [],
                parentName: $parentName,
                contentNode: $item->getExtra('content_node')
            );
        }

        return $result;
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
        if ($description === null || $description === '') {
            return null;
        }

        if ($item->getExtra(MenuUpdateInterface::IS_CUSTOM)) {
            return $description;
        }

        return $this->translator->trans($description);
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
