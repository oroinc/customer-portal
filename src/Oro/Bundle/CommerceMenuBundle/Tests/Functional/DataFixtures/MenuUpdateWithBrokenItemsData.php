<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Tests\Functional\DataFixtures\MenuUpdateTrait;
use Oro\Bundle\ScopeBundle\Tests\Functional\DataFixtures\LoadScopeData;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadContentNodesData;

class MenuUpdateWithBrokenItemsData extends AbstractFixture implements DependentFixtureInterface
{
    use UserUtilityTrait;
    use MenuUpdateTrait;

    const MENU_UPDATE_1 = 'global_menu_update.1';
    const MENU_UPDATE_2 = 'global_menu_update.2';
    const MENU_UPDATE_1_1 = 'global_menu_update.1_1';
    const MENU_UPDATE_2_1 = 'global_menu_update.2_1';

    /** @var array */
    protected static $menuUpdates = [
        'test_menu_item_url_global' => [
            'key' => 'test_menu_item_url',
            'parent_key' => null,
            'default_title' => 'test_menu_item_url.title',
            'titles' => [],
            'default_description' => 'test_menu_item_url.description',
            'descriptions' => [],
            'uri' => '#test_menu_item_url',
            'menu' => 'test_menu',
            'scope' => LoadScopeData::DEFAULT_SCOPE,
            'active' => true,
            'priority' => 10,
            'divider' => false,
            'custom' => true,
        ],
        'test_menu_item_content_node_global' => [
            'key' => 'test_menu_item_content_node',
            'parent_key' => null,
            'default_title' => 'test_menu_item_url.title',
            'titles' => [],
            'default_description' => 'test_menu_item_url.description',
            'descriptions' => [],
            'content_node' => LoadContentNodesData::CATALOG_1_ROOT_SUBNODE_1,
            'menu' => 'test_menu',
            'scope' => LoadScopeData::DEFAULT_SCOPE,
            'active' => true,
            'priority' => 10,
            'divider' => false,
            'custom' => true,
        ],
        'test_menu_item_system_route_global' => [
            'key' => 'test_menu_item_system_route',
            'parent_key' => null,
            'default_title' => 'test_menu_item_url.title',
            'titles' => [],
            'default_description' => 'test_menu_item_url.description',
            'descriptions' => [],
            'system_page_route' => 'test_route',
            'menu' => 'test_menu',
            'scope' => LoadScopeData::DEFAULT_SCOPE,
            'active' => true,
            'priority' => 10,
            'divider' => false,
            'custom' => true,
        ],
        'test_menu_item_url_user' => [
            'key' => 'test_menu_item_url',
            'parent_key' => null,
            'default_title' => 'test_menu_item_url.title',
            'titles' => [],
            'default_description' => 'test_menu_item_url.description',
            'descriptions' => [],
            'uri' => '#test_menu_item_url',
            'menu' => 'test_menu',
            'scope' => LoadScopeCustomerWebsiteData::WEBSITE_1_CUSTOMER_1_SCOPE,
            'active' => true,
            'priority' => 10,
            'divider' => false,
            'custom' => true,
        ],
        'test_menu_item_content_node_user' => [
            'key' => 'test_menu_item_content_node',
            'parent_key' => null,
            'default_title' => 'test_menu_item_url.title',
            'titles' => [],
            'default_description' => 'test_menu_item_url.description',
            'descriptions' => [],
            'content_node' => null,
            'menu' => 'test_menu',
            'scope' => LoadScopeCustomerWebsiteData::WEBSITE_1_CUSTOMER_1_SCOPE,
            'active' => true,
            'priority' => 10,
            'divider' => false,
            'custom' => true,
        ],
        'test_menu_item_system_route_user' => [
            'key' => 'test_menu_item_system_route',
            'parent_key' => null,
            'default_title' => 'test_menu_item_url.title',
            'titles' => [],
            'default_description' => 'test_menu_item_url.description',
            'descriptions' => [],
            'system_page_route' => 'test_route_2',
            'menu' => 'test_menu',
            'scope' => LoadScopeCustomerWebsiteData::WEBSITE_1_CUSTOMER_1_SCOPE,
            'active' => true,
            'priority' => 10,
            'divider' => false,
            'custom' => true,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadScopeData::class,
            LoadScopeCustomerWebsiteData::class,
            LoadContentNodesData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::$menuUpdates as $menuUpdateReference => $data) {
            $entity = $this->getMenuUpdateForCommerce($data, MenuUpdate::class);
            $this->setReference($menuUpdateReference, $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }

    private function getMenuUpdateForCommerce($data, $entityClass)
    {
        if (isset($data['content_node'])) {
            $data['content_node'] = $this->getReference($data['content_node']);
        }

        return $this->getMenuUpdate($data, $entityClass);
    }
}
