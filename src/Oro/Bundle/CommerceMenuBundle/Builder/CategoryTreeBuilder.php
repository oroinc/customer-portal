<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Menu\MenuCategoriesProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\NavigationBundle\MenuUpdateApplier\MenuUpdateApplier;
use Oro\Bundle\NavigationBundle\Utils\LostItemsManipulator;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Menu builder that takes the menu items with "category" extra option and expands their category tree
 * as per "max_traverse_level" extra option.
 */
class CategoryTreeBuilder implements BuilderInterface
{
    private ManagerRegistry $managerRegistry;

    private UrlGeneratorInterface $urlGenerator;

    private MenuCategoriesProviderInterface $menuCategoriesProvider;

    private TokenAccessorInterface $tokenAccessor;

    public function __construct(
        ManagerRegistry $managerRegistry,
        UrlGeneratorInterface $urlGenerator,
        MenuCategoriesProviderInterface $menuCategoriesProvider,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->urlGenerator = $urlGenerator;
        $this->menuCategoriesProvider = $menuCategoriesProvider;
        $this->tokenAccessor = $tokenAccessor;
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

        $category = $menuItem->getExtra(MenuUpdate::TARGET_CATEGORY);
        if (!$category instanceof Category) {
            return;
        }

        $includeSubcategories = (bool) $menuItem->getExtra('include_subcategories', true);
        $menuItem->setUri($this->getUrl($category->getId(), $includeSubcategories));

        $maxTraverseLevel = max(0, min(
            (int)$menuItem->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 0),
            MenuUpdateUtils::getAllowedNestingLevel($menuItem)
        ));
        $menuItem->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, $maxTraverseLevel);

        $user = $this->tokenAccessor->getUser();
        $categories = $this->menuCategoriesProvider
            ->getCategories($category, $user, null, ['tree_depth' => $maxTraverseLevel]);

        if (!$categories) {
            $menuItem->setDisplay(false);
            return;
        }

        $baseCategoryData = array_shift($categories);
        if (!$menuItem->getLabel() || $menuItem->getLabel() === $menuItem->getName()) {
            $menuItem->setLabel($baseCategoryData['title']);
        }

        $menuItem->setExtra('category_data', $baseCategoryData);

        $lostItems = LostItemsManipulator::getLostItemsContainer($menuItem, false)?->getChildren() ?? [];
        $prefixForChildren = $this->getPrefixForChildren($menuItem, $category->getId(), $lostItems);
        $this->addChildren($menuItem, $categories, $includeSubcategories, $prefixForChildren, $lostItems);
    }

    /**
     * @param ItemInterface $menuItem
     * @param iterable<array> $categories
     *  [
     *      int $categoryId => [
     *          'id' => int,
     *          'parentId' => int,
     *          'title' => string,
     *          'level' => int,
     *      ],
     *      // ...
     *  ]
     * @param bool $includeSubcategories
     * @param string $prefixForChildren
     * @param array<ItemInterface> $lostItems
     */
    private function addChildren(
        ItemInterface $menuItem,
        iterable $categories,
        bool $includeSubcategories,
        string $prefixForChildren,
        array $lostItems
    ): void {
        $addedMenuItems = [];
        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(Category::class);
        foreach ($categories as $categoryData) {
            $name = $prefixForChildren . $categoryData['id'];
            $child = $lostItems[$name] ?? null;
            if ($child) {
                // If the child is already created in the lost items container, mark it as custom to make it move
                // to its implied parent menu item.
                $child->setExtra(MenuUpdateApplier::IS_CUSTOM, true);
                $addedMenuItems[$name] = $child;
                continue;
            }

            $parentName = $prefixForChildren . $categoryData['parentId'];
            $parentMenuItem = $addedMenuItems[$parentName] ?? $menuItem;
            $maxTraverseLevel = (int)$parentMenuItem->getExtra('max_traverse_level', 0);

            $child = $parentMenuItem->addChild(
                $name,
                [
                    'label' => $categoryData['title'],
                    'uri' => $this->getUrl($categoryData['id'], $includeSubcategories),
                    'extras' => [
                        'isAllowed' => true,
                        'translate_disabled' => true,
                        'category_data' => $categoryData,
                        MenuUpdate::TARGET_CATEGORY => $entityManager
                            ->getReference(Category::class, $categoryData['id']),
                        MenuUpdate::MAX_TRAVERSE_LEVEL => max(0, $maxTraverseLevel - 1),
                    ],
                ]
            );

            $addedMenuItems[$name] = $child;
        }
    }

    private function getPrefixForChildren(ItemInterface $menuItem, int $categoryId, array $lostItems): string
    {
        $itemName = $menuItem->getName();
        if (isset($lostItems[$itemName])) {
            return substr($itemName, 0, strrpos($itemName, '_' . $categoryId)) . '_';
        }

        return $itemName . '_';
    }

    private function getUrl(int $categoryId, bool $includeSubcategories): string
    {
        return $this->urlGenerator->generate(
            'oro_product_frontend_product_index',
            ['categoryId' => $categoryId, 'includeSubcategories' => $includeSubcategories]
        );
    }
}
