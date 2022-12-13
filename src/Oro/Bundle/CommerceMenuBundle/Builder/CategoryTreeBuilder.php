<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Menu\MenuCategoriesProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
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

        $maxTraverseLevel = (int)$menuItem->getExtra('max_traverse_level', 0);
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

        $menuPrefix = $this->getMenuPrefix($menuItem, $category->getId());
        $this->addChildren($menuPrefix, $menuItem, $categories, $includeSubcategories);
    }

    /**
     * @param string $menuPrefix
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
     */
    private function addChildren(
        string $menuPrefix,
        ItemInterface $menuItem,
        iterable $categories,
        bool $includeSubcategories
    ): void {
        $addedMenuItems = [];
        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(Category::class);
        foreach ($categories as $categoryData) {
            $name = $menuPrefix . $categoryData['id'];
            $parentName = $menuPrefix . $categoryData['parentId'];

            $parentMenuItem = $addedMenuItems[$parentName] ?? $menuItem;
            $maxTraverseLevel = (int)$parentMenuItem->getExtra('max_traverse_level', 0);

            $child = $parentMenuItem->addChild(
                $name,
                [
                    'label' => $categoryData['title'],
                    'uri' => $this->getUrl($categoryData['id'], $includeSubcategories),
                    'extras' => [
                        'isAllowed' => true,
                        'category' => $entityManager->getReference(Category::class, $categoryData['id']),
                        'category_data' => $categoryData,
                        'max_traverse_level' => $maxTraverseLevel - 1,
                        // Max traverse level option should be disabled for synthetic menu items.
                        'max_traverse_level_disabled' => true,
                        'translate_disabled' => true,
                    ],
                ]
            );

            $addedMenuItems[$name] = $child;
        }
    }

    private function getMenuPrefix(ItemInterface $menuItem, int $categoryId): string
    {
        $menuPrefix = 'category_' . $categoryId;
        if ($menuItem->getName() !== $menuPrefix) {
            $menuPrefix = $menuItem->getName();
        }

        return $menuPrefix . '_';
    }

    private function getUrl(int $categoryId, bool $includeSubcategories): string
    {
        return $this->urlGenerator->generate(
            'oro_product_frontend_product_index',
            ['categoryId' => $categoryId, 'includeSubcategories' => $includeSubcategories]
        );
    }
}
