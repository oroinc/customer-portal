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
use Oro\Bundle\NavigationBundle\Provider\MenuUpdateProvider;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Adds 1st level categories from Master Catalog as menu items.
 */
class MasterCatalogRootItemsBuilder implements BuilderInterface
{
    private ManagerRegistry $managerRegistry;

    private TokenAccessorInterface $tokenAccessor;

    private MasterCatalogRootProviderInterface $masterCatalogRootProvider;

    private MenuCategoriesProviderInterface $menuCategoriesProvider;

    private WebCatalogProvider $webCatalogProvider;

    private MenuTemplatesProvider $menuTemplatesProvider;

    private string $targetMenuName = '';

    private array $extrasOption = [];

    public function __construct(
        ManagerRegistry $managerRegistry,
        TokenAccessorInterface $tokenAccessor,
        MasterCatalogRootProviderInterface $masterCatalogRootProvider,
        MenuCategoriesProviderInterface $menuCategoriesProvider,
        WebCatalogProvider $webCatalogProvider,
        MenuTemplatesProvider $menuTemplatesProvider
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->tokenAccessor = $tokenAccessor;
        $this->masterCatalogRootProvider = $masterCatalogRootProvider;
        $this->menuCategoriesProvider = $menuCategoriesProvider;
        $this->webCatalogProvider = $webCatalogProvider;
        $this->menuTemplatesProvider = $menuTemplatesProvider;
    }

    /**
     * Option "extras" to pass to the newly created menu items.
     */
    public function setExtras(array $extrasOption): void
    {
        $this->extrasOption = $extrasOption;
    }

    /**
     * The menu name to add master catalog categories to.
     */
    public function setTargetMenuName(string $targetMenuName): void
    {
        $this->targetMenuName = $targetMenuName;
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if ($menu->getName() !== $this->targetMenuName) {
            return;
        }

        if ($this->webCatalogProvider->getWebCatalog($this->getWebsite($options))) {
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
        $menuMaxNestingLevel = $menu->getExtra('max_nesting_level', 0);
        $menuTemplateName = $this->getFirstAvailableMenuTemplate();

        foreach ($categoriesData as $categoryData) {
            $menu->addChild(
                'category_' . $categoryData['id'],
                [
                    'label' => $categoryData['title'],
                    'extras' => array_merge([
                        'isAllowed' => true,
                        'category' => $entityManager->getReference(Category::class, $categoryData['id']),
                        'position' => -100,
                        'menu_template' => $menuTemplateName,
                        'max_traverse_level' => $menuMaxNestingLevel,
                    ], $this->extrasOption),
                ]
            );
        }
    }

    public function getWebsite(array $options): ?Website
    {
        $website = null;
        if (isset($options[MenuUpdateProvider::SCOPE_CONTEXT_OPTION])) {
            $scopeContext = $options[MenuUpdateProvider::SCOPE_CONTEXT_OPTION];
            if ($scopeContext instanceof Scope) {
                $website = $scopeContext->getWebsite();
            } elseif (isset($scopeContext['website']) && $scopeContext['website'] instanceof Website) {
                $website = $scopeContext['website'];
            }
        }

        return $website;
    }

    public function getFirstAvailableMenuTemplate(): string
    {
        $menuTemplateNames = array_keys($this->menuTemplatesProvider->getMenuTemplates());

        return reset($menuTemplateNames) ?: '';
    }
}
