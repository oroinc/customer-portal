<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\MenuUpdateWithBrokenItemsData;
use Oro\Bundle\NavigationBundle\Entity\Repository\MenuUpdateRepository;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class MenuUpdateRepositoryTest extends WebTestCase
{
    /** @var MenuUpdateRepository */
    protected $repository;

    protected function setUp(): void
    {
        $this->initClient();
        $this->client->useHashNavigation(true);

        $this->repository = $this
            ->getContainer()
            ->get('doctrine')
            ->getManagerForClass(\Oro\Bundle\NavigationBundle\Entity\MenuUpdate::class)
            ->getRepository(MenuUpdate::class);

        $this->loadFixtures([
            MenuUpdateWithBrokenItemsData::class
        ]);
    }

    /**
     * @dataProvider menuUpdateDataProvider
     * @param string $global
     * @param string $user
     */
    public function testUpdateDependentMenuUpdate(string $global, string $user)
    {
        /** @var MenuUpdate $globalMenuUpdate */
        $globalMenuUpdate = $this->getReference($global);
        /** @var MenuUpdate $userMenuUpdate */
        $userMenuUpdate = $this->getReference($user);

        $this->repository->updateDependentMenuUpdates($globalMenuUpdate);

        $this->assertEquals($globalMenuUpdate->getUri(), $userMenuUpdate->getUri());
        $this->assertEquals($globalMenuUpdate->getSystemPageRoute(), $userMenuUpdate->getSystemPageRoute());
        $this->assertEquals($globalMenuUpdate->getContentNode(), $userMenuUpdate->getContentNode());
    }

    public function menuUpdateDataProvider()
    {
        yield ['test_menu_item_url_global', 'test_menu_item_url_user'];
        yield ['test_menu_item_system_route_global', 'test_menu_item_system_route_user'];
        yield ['test_menu_item_content_node_global', 'test_menu_item_content_node_user'];
    }
}
