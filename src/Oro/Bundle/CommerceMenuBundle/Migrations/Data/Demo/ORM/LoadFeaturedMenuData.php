<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Loads the following items the storefront menu:
 * * Catalog
 * * Order History
 * * New Arrivals
 * * Contact us
 */
class LoadFeaturedMenuData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const MENU = 'featured_menu';
    const SCOPE_TYPE = 'menu_frontend_visibility';

    /** @var array */
    protected static $menuUpdates = [
        [
            'key' => 'featured_menu_catalog',
            'parent_key' => null,
            'default_title' => 'Catalog',
            'titles' => [],
            'default_description' => 'Browse a wide range of industrial, medical, and office supplies'
                . ' in our product catalog.',
            'descriptions' => [],
            'uri' => '/product',
            'menu' => self::MENU,
            'active' => true,
            'scope' => self::SCOPE_TYPE,
            'priority' => 10,
            'divider' => false,
            'custom' => true,
            'icon' => 'fa-book',
            'condition' => '',
            'screens' => [],
        ],
        [
            'key' => 'featured_menu_order_history',
            'parent_key' => null,
            'default_title' => 'Order History',
            'titles' => [],
            'default_description' => 'Keep track of all current and submitted orders under your account menu.',
            'descriptions' => [],
            'uri' => '/customer/order',
            'menu' => self::MENU,
            'active' => true,
            'priority' => 20,
            'divider' => false,
            'custom' => true,
            'icon' => 'fa-list-alt',
            'condition' => '',
            'screens' => [],
        ],
        [
            'key' => 'featured_menu_new_arrivals',
            'parent_key' => null,
            'default_title' => 'New Arrivals',
            'titles' => [],
            'default_description' => 'Shop a selection of current new arrivals.'
                . ' You will find the products of the latest trends.',
            'descriptions' => [],
            'uri' => '/navigation-root/new-arrivals',
            'menu' => self::MENU,
            'active' => true,
            'priority' => 30,
            'divider' => false,
            'custom' => true,
            'icon' => 'fa-gift',
            'condition' => '',
            'screens' => [],
        ],
        [
            'key' => 'featured_menu_contact_us',
            'parent_key' => null,
            'default_title' => 'Contact us',
            'titles' => [],
            'default_description' => 'Need assistance with a product, checkout, or your order? Contact us for help.',
            'descriptions' => [],
            'uri' => '/contact-us',
            'menu' => self::MENU,
            'active' => true,
            'priority' => 40,
            'divider' => false,
            'custom' => true,
            'icon' => 'fa-envelope',
            'condition' => '',
            'screens' => [],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $scope = $this->getScope();

        foreach (self::$menuUpdates as $menuUpdateData) {
            $menuUpdateData = ['scope' => $scope] + $menuUpdateData;
            /** @var MenuUpdate $menuUpdate */
            $menuUpdate = $this->createMenuUpdate($menuUpdateData);
            $manager->persist($menuUpdate);
        }

        $manager->flush();
    }

    /**
     * @return Scope
     */
    protected function getScope()
    {
        /** @var ScopeManager $scopeManager */
        $scopeManager = $this->container->get('oro_scope.scope_manager');

        return $scopeManager->findOrCreate('menu_frontend_visibility', []);
    }

    protected function createMenuUpdate(array $data): MenuUpdate
    {
        $menuUpdate = new MenuUpdate();

        $menuUpdate->setKey($data['key']);
        $menuUpdate->setParentKey($data['parent_key']);
        $menuUpdate->setUri($data['uri']);
        $menuUpdate->setMenu($data['menu']);
        $menuUpdate->setActive($data['active']);
        $menuUpdate->setScope($data['scope']);
        $menuUpdate->setPriority($data['priority']);
        $menuUpdate->setDivider($data['divider']);
        $menuUpdate->setCustom($data['custom']);
        $menuUpdate->setIcon($data['icon']);
        $menuUpdate->setCondition($data['condition']);
        $menuUpdate->setScreens($data['screens']);

        $menuUpdate->setDefaultTitle($data['default_title']);
        $menuUpdate->setDefaultDescription($data['default_description']);

        return $menuUpdate;
    }
}
