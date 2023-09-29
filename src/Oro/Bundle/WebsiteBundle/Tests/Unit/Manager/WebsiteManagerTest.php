<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MaintenanceBundle\Maintenance\MaintenanceModeState;
use Oro\Bundle\MaintenanceBundle\Maintenance\MaintenanceRestrictionsChecker;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteManagerTest extends TestCase
{
    private ManagerRegistry|MockObject $managerRegistry;

    private FrontendHelper|MockObject $frontendHelper;

    private MaintenanceModeState|MockObject $maintenanceModeState;

    private WebsiteManager $manager;

    private MaintenanceRestrictionsChecker|MockObject $maintenanceRestrictionsChecker;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->maintenanceModeState = $this->createMock(MaintenanceModeState::class);
        $this->maintenanceRestrictionsChecker = $this->createMock(MaintenanceRestrictionsChecker::class);

        $this->manager = new WebsiteManager(
            $this->managerRegistry,
            $this->frontendHelper,
            $this->maintenanceModeState,
            $this->maintenanceRestrictionsChecker
        );
    }

    public function testGetCurrentWebsite(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $repository = $this->createMock(WebsiteRepository::class);

        $website = new Website();
        $repository->expects(self::once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::once())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($repository);

        $this->managerRegistry->expects(self::once())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($objectManager);

        self::assertSame($website, $this->manager->getCurrentWebsite());
    }

    public function testGetCurrentWebsiteWithEnabledMaintenanceMode(): void
    {
        $this
            ->maintenanceModeState
            ->expects(self::once())
            ->method('isOn')
            ->willReturn(true);

        $this
            ->maintenanceRestrictionsChecker
            ->expects(self::once())
            ->method('isAllowed')
            ->willReturn(false);

        $this->frontendHelper->expects(self::never())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $repository = $this->createMock(WebsiteRepository::class);

        $website = new Website();
        $repository->expects(self::never())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::never())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($repository);

        $this->managerRegistry->expects(self::never())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($objectManager);

        self::assertEquals(null, $this->manager->getCurrentWebsite());
    }

    public function testGetDefaultWebsite(): void
    {
        $this->frontendHelper->expects($this->never())
            ->method('isFrontendRequest');

        $repository = $this->createMock(WebsiteRepository::class);

        $website = new Website();
        $repository->expects(self::once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::once())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($repository);

        $this->managerRegistry->expects(self::once())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($objectManager);

        self::assertSame($website, $this->manager->getDefaultWebsite());
    }

    public function testGetCurrentWebsiteNonFrontend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->managerRegistry->expects($this->never())
            ->method('getManagerForClass');

        self::assertNull($this->manager->getCurrentWebsite());
    }

    public function testSetCurrentWebsite(): void
    {
        $this->manager->setCurrentWebsite($website = $this->createMock(Website::class));

        self::assertSame($website, $this->manager->getCurrentWebsite());
    }

    public function testOnClear(): void
    {
        ReflectionUtil::setPropertyValue($this->manager, 'currentWebsite', new Website());
        $this->manager->onClear();
        self::assertEmpty(ReflectionUtil::getPropertyValue($this->manager, 'currentWebsite'));
    }

    public function testGetCurrentWebsiteWhenMaintenanceMode(): void
    {
        $this->maintenanceModeState->expects(self::once())
            ->method('isOn')
            ->willReturn(true);

        $this->managerRegistry->expects($this->never())
            ->method('getManagerForClass');

        self::assertNull($this->manager->getCurrentWebsite());
    }
}
