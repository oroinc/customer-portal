<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Menu\MenuCategoriesProviderInterface;
use Oro\Bundle\CatalogBundle\Provider\MasterCatalogRootProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Adds to menu the 1st level categories from Master Catalog.
 */
class MasterCatalogNavigationRootBuilder implements BuilderInterface
{
    private ManagerRegistry $managerRegistry;

    private TokenAccessorInterface $tokenAccessor;

    private MasterCatalogRootProviderInterface $masterCatalogRootProvider;

    private MenuCategoriesProviderInterface $menuCategoriesProvider;

    private MenuTemplatesProvider $menuTemplatesProvider;

    private array $extrasOption = [];

    public function __construct(
        ManagerRegistry $managerRegistry,
        TokenAccessorInterface $tokenAccessor,
        MasterCatalogRootProviderInterface $masterCatalogRootProvider,
        MenuCategoriesProviderInterface $menuCategoriesProvider,
        MenuTemplatesProvider $menuTemplatesProvider
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->tokenAccessor = $tokenAccessor;
        $this->masterCatalogRootProvider = $masterCatalogRootProvider;
        $this->menuCategoriesProvider = $menuCategoriesProvider;
        $this->menuTemplatesProvider = $menuTemplatesProvider;
    }

    /**
     * Option "extras" to pass to the newly created menu items.
     */
    public function setExtras(array $extrasOption): void
    {
        $this->extrasOption = $extrasOption;
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if (!$menu->isDisplayed()) {
            return;
        }

        $rootCategory = $this->masterCatalogRootProvider->getMasterCatalogRoot();
        $user = $this->tokenAccessor->getUser();
        $categoriesData = $this->menuCategoriesProvider->getCategories($rootCategory, $user, null, ['tree_depth' => 1]);
        if (!$categoriesData) {
            return;
        }

        // Shifts the root category.
        array_shift($categoriesData);

        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(Category::class);
        $maxNestingLevel = $menu->getExtra('max_nesting_level', 1);
        $maxTraverseLevel = $maxNestingLevel > 0 ? $maxNestingLevel - 1 : 0;
        $menuTemplateName = $this->getFirstAvailableMenuTemplate();

        $startingPosition = -100 - count($categoriesData);
        foreach (array_reverse($categoriesData) as $categoryData) {
            $menu->addChild(
                'category_' . $categoryData['id'],
                [
                    'label' => $categoryData['title'],
                    'extras' => array_merge([
                        'isAllowed' => true,
                        'category' => $entityManager->getReference(Category::class, $categoryData['id']),
                        'position' => $startingPosition++,
                        'menu_template' => $menuTemplateName,
                        'max_traverse_level' => $maxTraverseLevel,
                        'translate_disabled' => true,
                    ], $this->extrasOption),
                ]
            );
        }
    }

    public function getFirstAvailableMenuTemplate(): string
    {
        $menuTemplateNames = array_keys($this->menuTemplatesProvider->getMenuTemplates());

        return reset($menuTemplateNames) ?: '';
    }
}
