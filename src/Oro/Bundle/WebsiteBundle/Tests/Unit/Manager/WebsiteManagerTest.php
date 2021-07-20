<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MaintenanceBundle\Maintenance\Mode;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class WebsiteManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var WebsiteManager */
    protected $manager;

    /** @var FrontendHelper */
    protected $frontendHelper;

    /** @var Mode */
    private $maintenance;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->maintenance = $this->createMock(Mode::class);

        $this->manager = new WebsiteManager($this->managerRegistry, $this->frontendHelper, $this->maintenance);
    }

    protected function tearDown(): void
    {
        unset($this->managerRegistry, $this->manager, $this->frontendHelper);
    }

    public function testGetCurrentWebsite()
    {
        $this->frontendHelper->expects(static::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $repository = $this->getMockBuilder(WebsiteRepository::class)->disableOriginalConstructor()->getMock();

        $website = new Website();
        $repository->expects(static::once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(static::once())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($repository);

        $this->managerRegistry->expects(static::once())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($objectManager);

        static::assertSame($website, $this->manager->getCurrentWebsite());
    }

    public function testGetDefaultWebsite()
    {
        $this->frontendHelper->expects($this->never())
            ->method('isFrontendRequest');

        $repository = $this->getMockBuilder(WebsiteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $website = new Website();
        $repository->expects(static::once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(static::once())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($repository);

        $this->managerRegistry->expects(static::once())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($objectManager);

        static::assertSame($website, $this->manager->getDefaultWebsite());
    }

    public function testGetCurrentWebsiteNonFrontend()
    {
        $this->frontendHelper->expects(static::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->managerRegistry->expects($this->never())
            ->method('getManagerForClass');

        static::assertNull($this->manager->getCurrentWebsite());
    }

    public function testSetCurrentWebsite()
    {
        $this->manager->setCurrentWebsite($website = $this->createMock(Website::class));

        static::assertSame($website, $this->manager->getCurrentWebsite());
    }

    public function testOnClear()
    {
        $manager = new class($this->managerRegistry, $this->frontendHelper, $this->maintenance) extends WebsiteManager {
            public function xgetCurrentWebsite(): ?Website
            {
                return $this->currentWebsite;
            }

            public function xsetCurrentWebsite(Website $currentWebsite): void
            {
                $this->currentWebsite = $currentWebsite;
            }
        };

        $manager->xsetCurrentWebsite(new Website());
        $manager->onClear();
        static::assertEmpty($manager->xgetCurrentWebsite());
    }

    public function testGetCurrentWebsiteWhenMaintenanceMode(): void
    {
        $this->maintenance
            ->expects(static::once())
            ->method('isOn')
            ->willReturn(true);

        $this->managerRegistry
            ->expects($this->never())
            ->method('getManagerForClass');

        static::assertNull($this->manager->getCurrentWebsite());
    }
}
