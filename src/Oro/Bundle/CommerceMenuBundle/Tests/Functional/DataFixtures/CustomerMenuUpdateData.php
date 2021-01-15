<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\LocaleBundle\Tests\Functional\DataFixtures\LoadLocalizationData;
use Oro\Bundle\NavigationBundle\Tests\Functional\DataFixtures\MenuUpdateTrait;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;

class CustomerMenuUpdateData extends AbstractFixture implements DependentFixtureInterface
{
    use UserUtilityTrait;
    use MenuUpdateTrait;

    const MENU_UPDATE_1 = 'customer_menu_update.1';
    const MENU_UPDATE_1_1 = 'customer_menu_update.1_1';

    /** @var array */
    protected static $menuUpdates = [
        self::MENU_UPDATE_1 => [
            'key' => self::MENU_UPDATE_1,
            'parent_key' => null,
            'default_title' => self::MENU_UPDATE_1 . '.title',
            'titles' => [
                'en_US' => self::MENU_UPDATE_1 . '.title.en_US',
                'en_CA' => self::MENU_UPDATE_1 . '.title.en_CA',
            ],
            'default_description' => self::MENU_UPDATE_1 . '.description',
            'descriptions' => [
                'en_US' => self::MENU_UPDATE_1 . '.description.en_US',
                'en_CA' => self::MENU_UPDATE_1 . '.description.en_CA',
            ],
            'uri' => '#' . self::MENU_UPDATE_1,
            'menu' => 'frontend_menu',
            'scope' => LoadScopeCustomerWebsiteData::WEBSITE_1_CUSTOMER_1_SCOPE,
            'active' => true,
            'priority' => 10,
            'divider' => false,
            'custom' => true,
        ],
        self::MENU_UPDATE_1_1 => [
            'key' => self::MENU_UPDATE_1_1,
            'parent_key' => self::MENU_UPDATE_1,
            'default_title' => self::MENU_UPDATE_1_1 . '.title',
            'titles' => [],
            'default_description' => self::MENU_UPDATE_1_1 . '.description',
            'descriptions' => [],
            'uri' => '#' . self::MENU_UPDATE_1_1,
            'menu' => 'frontend_menu',
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
            LoadLocalizationData::class,
            LoadScopeCustomerWebsiteData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::$menuUpdates as $menuUpdateReference => $data) {
            $entity = $this->getMenuUpdate($data, MenuUpdate::class);
            $this->setReference($menuUpdateReference, $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
