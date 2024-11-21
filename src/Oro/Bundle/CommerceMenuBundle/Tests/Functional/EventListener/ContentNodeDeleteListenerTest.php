<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Tests\Functional\DataFixtures\MenuUpdateData;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadContentNodesData;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ContentNodeDeleteListenerTest extends WebTestCase
{
    private CacheInterface $cache;
    private EntityManagerInterface $em;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([
            MenuUpdateData::class,
            LoadCustomers::class,
            LoadContentNodesData::class
        ]);

        $this->cache = self::getContainer()->get('oro_navigation.menu_update.cache');
        $this->em = self::getContainer()->get('doctrine')->getManagerForClass(ContentNode::class);
    }

    public function testPostRemove()
    {
        /** @var MenuUpdate $menuUpdate */
        $menuUpdate = $this->getReference(MenuUpdateData::MENU_UPDATE_1);
        $notInvolvedCustomer = $this->getReference(LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1);
        $contentNode = $this->getReference(LoadContentNodesData::CATALOG_1_ROOT);

        // Fill cache
        $cacheKey = MenuUpdateUtils::generateKey($menuUpdate->getMenu(), $menuUpdate->getScope());
        $this->cache->get(
            $cacheKey,
            function () use ($menuUpdate) {
                return [$menuUpdate];
            }
        );

        // Check cache not removed on any entity removal
        $this->em->remove($notInvolvedCustomer);
        $this->em->flush();

        self::assertNotEmpty(
            $this->cache->get(
                $cacheKey,
                function (ItemInterface $item) {
                    self::assertTrue($item->isHit(), 'Cache not filled, no hit');

                    return $item->get();
                }
            ),
            'Cache removed unexpectedly'
        );

        // Check cache is removed on ContentNode entity removal
        $this->em->remove($contentNode);
        $this->em->flush();

        self::assertEmpty(
            $this->cache->get(
                $cacheKey,
                function (ItemInterface $item) {
                    self::assertFalse($item->isHit(), 'Cache filled when should be empty');

                    return null;
                }
            ),
            'Cache not cleared'
        );
    }
}
