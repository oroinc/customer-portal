<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Provider\MasterCatalogRootProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebCatalogBundle\Migrations\Data\Demo\ORM\LoadWebCatalogDemoData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Loads menu updates demo data for category menu items on the 1st level.
 */
class LoadCategoryMenuUpdateDemoData extends AbstractMenuUpdateDemoFixture implements DependentFixtureInterface
{
    use UserUtilityTrait;

    private ?TokenStorageInterface $tokenStorage = null;

    private ?ManagerRegistry $managerRegistry = null;

    private ?MasterCatalogRootProviderInterface $masterCatalogRootProvider = null;

    private ?UrlGeneratorInterface $router = null;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->tokenStorage = $container->get('security.token_storage');
        $this->managerRegistry = $container->get('doctrine');
        $this->masterCatalogRootProvider = $container->get('oro_catalog_pro.provider.master_catalog_root');
        $this->router = $container->get('router');
    }

    public function load(ObjectManager $manager): void
    {
        $previousToken = $this->tokenStorage->getToken();
        $this->setSecurityContext($this->tokenStorage, $this->getFirstUser($manager));

        $this->createMenuUpdatesForCategories($manager);

        $manager->flush();

        $this->tokenStorage->setToken($previousToken);
    }

    public function getDependencies()
    {
        return [
            LoadWebCatalogDemoData::class,
        ];
    }

    private function createMenuUpdatesForCategories(ObjectManager $manager): void
    {
        $categories = $this->getCategories();
        $startingPosition = -100 - count($categories);
        $itemNumber = $itemConfigNumber = 1;
        foreach ($categories as $category) {
            $menuUpdateData = [
                'category' => $category,
                'maxTraverseLevel' => self::ITEM_CONFIGS_BY_ITEM_NUMBER[$itemConfigNumber]['maxTraverseLevel'],
                'priority' => $startingPosition++,
                'menuTemplate' => self::ITEM_CONFIGS_BY_ITEM_NUMBER[$itemConfigNumber]['menuTemplate'],
                'uri' => $this->getCategoryUrl($category->getId()),
                'title' => $this->getLocalizedValue($category->getTitles()),
            ];
            $menuUpdate = $this->createMenuUpdate($menuUpdateData);
            $manager->persist($menuUpdate);

            $this->setReference('menu_update_category_' . $itemNumber, $menuUpdate);

            $itemNumber++;
            $itemConfigNumber = $itemConfigNumber >= 3 ? $itemConfigNumber : $itemConfigNumber + 1;
        }
    }

    protected function createMenuUpdate(array $data): MenuUpdate
    {
        $menuUpdate = parent::createMenuUpdate($data);

        $menuUpdate->setCategory($data['category']);
        $menuUpdate->setKey('category_' . $data['category']->getId());

        return $menuUpdate;
    }

    private function setSecurityContext(TokenStorageInterface $tokenStorage, User $user): void
    {
        $tokenStorage->setToken(new UsernamePasswordOrganizationToken(
            $user,
            $user->getUsername(),
            'main',
            $user->getOrganization(),
            $user->getUserRoles()
        ));
    }

    private function getCategories(): array
    {
        $root = $this->masterCatalogRootProvider->getMasterCatalogRoot();

        return $this->managerRegistry->getManagerForClass(Category::class)
            ->getRepository(Category::class)
            ->getChildren($root, true, 'left');
    }

    private function getCategoryUrl(int $categoryId): string
    {
        return $this->router->generate(
            'oro_product_frontend_product_index',
            ['categoryId' => $categoryId, 'includeSubcategories' => true]
        );
    }
}
