<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MaintenanceBundle\Maintenance\MaintenanceModeState;
use Oro\Bundle\MaintenanceBundle\Maintenance\MaintenanceRestrictionsChecker;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\ReflectionUtil;

class WebsiteManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var MaintenanceModeState|\PHPUnit\Framework\MockObject\MockObject */
    private $maintenanceModeState;

    /** @var MaintenanceRestrictionsChecker|\PHPUnit\Framework\MockObject\MockObject */
    private $maintenanceRestrictionsChecker;

    /** @var WebsiteManager */
    private $websiteManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->maintenanceModeState = $this->createMock(MaintenanceModeState::class);
        $this->maintenanceRestrictionsChecker = $this->createMock(MaintenanceRestrictionsChecker::class);

        $this->websiteManager = new WebsiteManager(
            $this->doctrine,
            $this->frontendHelper,
            $this->maintenanceModeState,
            $this->maintenanceRestrictionsChecker
        );
    }

    private function expectGetDefaultWebsite(Website $website): void
    {
        $repository = $this->createMock(WebsiteRepository::class);
        $repository->expects(self::once())
            ->method('getDefaultWebsite')
            ->willReturn($website);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($repository);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($em);
    }

    public function testGetCurrentWebsite(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->maintenanceModeState->expects(self::once())
            ->method('isOn')
            ->willReturn(false);
        $this->maintenanceRestrictionsChecker->expects(self::never())
            ->method('isAllowed');

        $website = new Website();

        $this->expectGetDefaultWebsite($website);

        self::assertSame($website, $this->websiteManager->getCurrentWebsite());
        // test memory cache
        self::assertSame($website, $this->websiteManager->getCurrentWebsite());
    }

    public function testGetCurrentWebsiteNonFrontend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->maintenanceModeState->expects(self::never())
            ->method('isOn');
        $this->maintenanceRestrictionsChecker->expects(self::never())
            ->method('isAllowed');

        $this->doctrine->expects($this->never())
            ->method('getManagerForClass');

        self::assertNull($this->websiteManager->getCurrentWebsite());
    }

    public function testGetCurrentWebsiteWithEnabledMaintenanceMode(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->maintenanceModeState->expects(self::once())
            ->method('isOn')
            ->willReturn(true);
        $this->maintenanceRestrictionsChecker->expects(self::once())
            ->method('isAllowed')
            ->willReturn(false);

        $this->doctrine->expects($this->never())
            ->method('getManagerForClass');

        self::assertNull($this->websiteManager->getCurrentWebsite());
    }


    public function testGetCurrentWebsiteWithEnabledMaintenanceModeAndAccessAllowed(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->maintenanceModeState->expects(self::once())
            ->method('isOn')
            ->willReturn(true);
        $this->maintenanceRestrictionsChecker->expects(self::once())
            ->method('isAllowed')
            ->willReturn(true);

        $website = new Website();

        $this->expectGetDefaultWebsite($website);

        self::assertSame($website, $this->websiteManager->getCurrentWebsite());
        // test memory cache
        self::assertSame($website, $this->websiteManager->getCurrentWebsite());
    }

    public function testGetDefaultWebsite(): void
    {
        $this->frontendHelper->expects($this->never())
            ->method('isFrontendRequest');

        $this->maintenanceModeState->expects(self::never())
            ->method('isOn');
        $this->maintenanceRestrictionsChecker->expects(self::never())
            ->method('isAllowed');

        $website = new Website();

        $this->expectGetDefaultWebsite($website);

        self::assertSame($website, $this->websiteManager->getDefaultWebsite());
    }

    public function testSetCurrentWebsite(): void
    {
        $website = $this->createMock(Website::class);
        $this->websiteManager->setCurrentWebsite($website);

        self::assertSame($website, $this->websiteManager->getCurrentWebsite());
    }

    public function testOnClear(): void
    {
        ReflectionUtil::setPropertyValue($this->websiteManager, 'currentWebsite', new Website());
        $this->websiteManager->onClear();
        self::assertNull(ReflectionUtil::getPropertyValue($this->websiteManager, 'currentWebsite'));
    }
}
