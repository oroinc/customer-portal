<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Menu\MenuCategoriesProviderInterface;
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
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Menu builder that takes the menu items with "category" extra option and expands their category tree
 * as per "max_traverse_level" extra option.
 */
class CategoryTreeBuilder implements BuilderInterface
{
    public const IS_TREE_ITEM = 'category_tree_item';
    public const TREE_ITEM_OPTIONS = 'category_tree_item_options';
    public const CATEGORY_DATA = 'category_data';
    public const INCLUDE_SUBCATEGORIES = 'include_subcategories';

    private ManagerRegistry $managerRegistry;

    private UrlGeneratorInterface $urlGenerator;

    private MenuCategoriesProviderInterface $menuCategoriesProvider;

    private TokenAccessorInterface $tokenAccessor;

    private LocalizationHelper $localizationHelper;

    /**
     * @var array<string,MenuUpdateApplierContext> Contexts indexed by menu name.
     */
    private array $menuUpdateApplierContexts = [];

    public function __construct(
        ManagerRegistry $managerRegistry,
        UrlGeneratorInterface $urlGenerator,
        MenuCategoriesProviderInterface $menuCategoriesProvider,
        TokenAccessorInterface $tokenAccessor,
        LocalizationHelper $localizationHelper
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->urlGenerator = $urlGenerator;
        $this->menuCategoriesProvider = $menuCategoriesProvider;
        $this->tokenAccessor = $tokenAccessor;
        $this->localizationHelper = $localizationHelper;
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        $menuItemsByName = MenuUpdateUtils::flattenMenuItem($menu);
        $maxNestingLevel = max(0, (int)$menu->getExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 0));
        $user = $this->tokenAccessor->getUser();

        $this->applyRecursively(
            $menu,
            $menuItemsByName,
            $options,
            $maxNestingLevel,
            $user,
            $this->menuUpdateApplierContexts[$menu->getName()] ?? null
        );
    }

    private function applyRecursively(
        ItemInterface $menuItem,
        array &$menuItemsByName,
        array $menuOptions,
        int $maxNestingLevel,
        ?UserInterface $user,
        ?MenuUpdateApplierContext $menuUpdateApplierContext
    ): void {
        foreach ($menuItem->getChildren() as $menuChild) {
            $this->applyRecursively(
                $menuChild,
                $menuItemsByName,
                $menuOptions,
                $maxNestingLevel,
                $user,
                $menuUpdateApplierContext
            );
        }

        $category = $menuItem->getExtra(MenuUpdate::TARGET_CATEGORY);
        if (!$category instanceof Category) {
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

        $categories = $this->menuCategoriesProvider
            ->getCategories($category, $user, ['tree_depth' => $maxTraverseLevel]);
        if (!$categories) {
            $menuItem->setDisplay(false);
            return;
        }

        $baseCategoryData = array_shift($categories);

        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(Category::class);

        // Explicit passing of localization avoids further unnecessary calls to getCurrentLocalization.
        $localization = $this->localizationHelper->getCurrentLocalization();
        $includeSubcategories = (bool)$menuItem->getExtra(self::INCLUDE_SUBCATEGORIES, true);

        if (!$menuItem->isRoot()) {
            $this->setMenuItemData(
                $menuItem,
                $baseCategoryData,
                $entityManager,
                $localization,
                $includeSubcategories,
            );
        }

        $this->setAllowedTraverseLevel($menuItem, $maxTraverseLevel);

        $treeItemNamePrefix = self::getTreeItemNamePrefix($menuItem, $category->getId());
        $menuItemsByName[$treeItemNamePrefix . $category->getId()] = $menuItem;

        $this->addTreeItems(
            $categories,
            $entityManager,
            $localization,
            $includeSubcategories,
            $treeItemNamePrefix,
            $menuItemsByName,
            $menuOptions,
            $menuUpdateApplierContext
        );
    }

    /**
     * @param iterable<array> $categories
     *  [
     *      int $categoryId => [
     *          'id' => int,
     *          'parentId' => int,
     *          'titles' => Collection<LocalizedFallbackValue>,
     *          'level' => int,
     *      ],
     *      // ...
     *  ]
     * @param EntityManager $entityManager
     * @param Localization|null $localization
     * @param bool $includeSubcategories
     * @param string $treeItemNamePrefix
     * @param array<ItemInterface> $menuItemsByName
     * @param array $menuOptions
     * @param MenuUpdateApplierContext|null $menuUpdateApplierContext
     */
    private function addTreeItems(
        iterable $categories,
        EntityManager $entityManager,
        ?Localization $localization,
        bool $includeSubcategories,
        string $treeItemNamePrefix,
        array &$menuItemsByName,
        array $menuOptions,
        ?MenuUpdateApplierContext $menuUpdateApplierContext
    ): void {
        $indexesByParent = [];
        $syntheticItemNames = [];
        $menuOptions['extras'][self::IS_TREE_ITEM] = true;

        foreach ($categories as $categoryData) {
            $parentName = $treeItemNamePrefix . $categoryData['parentId'];
            $indexesByParent[$parentName] = $indexesByParent[$parentName] ?? 0;
            $parentMenuItem = $menuItemsByName[$parentName] ?? null;
            if ($parentMenuItem === null) {
                // Skips child as its parent is not found. Likely it was not added due to exceeded max traverse level.
                continue;
            }

            if (in_array($parentName, $syntheticItemNames, true)) {
                // Skips child as its parent is synthetic and should be processed separately.
                continue;
            }

            $parentMaxTraverseLevel = max(0, (int)$parentMenuItem->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 0));
            if ($parentMaxTraverseLevel === 0) {
                // Skips child as parent's max traverse level does not allow more.
                continue;
            }

            $name = $treeItemNamePrefix . $categoryData['id'];
            if (isset($menuItemsByName[$name])) {
                if ($menuItemsByName[$name]->getExtra(MenuUpdateInterface::IS_SYNTHETIC)) {
                    $syntheticItemNames[] = $name;
                    continue;
                }

                $menuUpdateApplierContext?->removeLostItem($name);
                $menuItemsByName[$name]->setExtra(self::IS_TREE_ITEM, true);
            } else {
                $options = array_merge_recursive($menuOptions, $parentMenuItem->getExtra(self::TREE_ITEM_OPTIONS, []));
                $options['extras'][MenuUpdateInterface::POSITION] = $indexesByParent[$parentName];
                $menuItemsByName[$name] = $parentMenuItem->addChild($name, $options);
            }

            $indexesByParent[$parentName]++;

            $this->setMenuItemData(
                $menuItemsByName[$name],
                $categoryData,
                $entityManager,
                $localization,
                $includeSubcategories
            );

            $this->setAllowedTraverseLevel($menuItemsByName[$name], $parentMaxTraverseLevel - 1);
        }
    }

    private function setMenuItemData(
        ItemInterface $menuItem,
        array $categoryData,
        EntityManager $entityManager,
        ?Localization $localization,
        bool $includeSubcategories
    ): void {
        $menuItem->setUri($this->generateUrl($categoryData['id'], $includeSubcategories));

        if ($menuItem->getLabel() === $menuItem->getName()) {
            $menuItem->setLabel($this->getLabel($categoryData['titles'], $localization));
        }

        $menuItem->setExtra(
            MenuUpdateInterface::TITLES,
            LocalizedFallbackValueHelper::cloneCollection($categoryData['titles'])
        );

        $menuItem->setExtra(
            MenuUpdate::TARGET_CATEGORY,
            $entityManager->getReference(Category::class, $categoryData['id'])
        );

        $menuItem->setExtra(self::CATEGORY_DATA, $categoryData);

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

    private function generateUrl(int $categoryId, bool $includeSubcategories): string
    {
        return $this->urlGenerator->generate(
            'oro_product_frontend_product_index',
            ['categoryId' => $categoryId, 'includeSubcategories' => $includeSubcategories]
        );
    }

    private function getLabel(Collection $titles, ?Localization $localization): string
    {
        return (string)$this->localizationHelper->getLocalizedValue($titles, $localization);
    }

    public static function getTreeItemNamePrefix(ItemInterface $menuItem, int $categoryId): string
    {
        $itemName = $menuItem->getName();
        $idPosition = strrpos($itemName, '__' . $categoryId);
        if ($idPosition !== false) {
            return substr($itemName, 0, $idPosition) . '__';
        }

        return 'menu_item_' . sha1('category_' . $itemName) . '__';
    }

    public function onMenuUpdatesApplyAfter(MenuUpdatesApplyAfterEvent $event): void
    {
        $menuUpdateApplierContext = $event->getContext();
        $this->menuUpdateApplierContexts[$menuUpdateApplierContext->getMenu()->getName()] = $menuUpdateApplierContext;
    }
}
