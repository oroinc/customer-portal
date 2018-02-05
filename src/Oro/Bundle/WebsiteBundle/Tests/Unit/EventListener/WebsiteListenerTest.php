<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\EventListener\WebsiteListener;
use Oro\Bundle\WebsiteBundle\Provider\CacheableWebsiteProvider;

class WebsiteListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var CacheableWebsiteProvider|\PHPUnit_Framework_MockObject_MockObject */
    private $cacheableProvider;

    /** @var WebsiteListener */
    private $listener;

    /** @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject */
    private $uow;

    protected function setUp()
    {
        $this->cacheableProvider = $this->createMock(CacheableWebsiteProvider::class);

        $this->listener = new WebsiteListener($this->cacheableProvider);

        $this->uow = $this->createMock(UnitOfWork::class);
    }

    public function testOnFlushWhenCacheIsEmpty()
    {
        /** @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->createMock(OnFlushEventArgs::class);
        $args->expects($this->never())
            ->method('getEntityManager');

        $this->cacheableProvider->expects($this->once())
            ->method('hasCache')
            ->willReturn(false);
        $this->cacheableProvider->expects($this->never())
            ->method('clearCache');

        $this->listener->onFlush($args);
    }

    public function testOnFlushWhenHasCacheAndNoScheduledWebsite()
    {
        $this->cacheableProvider->expects($this->once())
            ->method('hasCache')
            ->willReturn(true);
        $this->cacheableProvider->expects($this->never())
            ->method('clearCache');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([new \stdClass()]);
        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([new \stdClass()]);
        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([new \stdClass()]);

        $this->listener->onFlush($this->getEventArgs());
    }

    public function testOnFlushWhenHasCacheAndHasInsertedWebsite()
    {
        $this->cacheableProvider->expects($this->once())
            ->method('hasCache')
            ->willReturn(true);
        $this->cacheableProvider->expects($this->once())
            ->method('clearCache');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([$this->createMock(Website::class)]);
        $this->uow->expects($this->never())
            ->method('getScheduledEntityUpdates');
        $this->uow->expects($this->never())
            ->method('getScheduledEntityDeletions');


        $this->listener->onFlush($this->getEventArgs());
    }

    public function testOnFlushWhenHasCacheAndHasUpdatedWebsite()
    {
        $this->cacheableProvider->expects($this->once())
            ->method('hasCache')
            ->willReturn(true);
        $this->cacheableProvider->expects($this->once())
            ->method('clearCache');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([new \stdClass()]);
        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([$this->createMock(Website::class)]);
        $this->uow->expects($this->never())
            ->method('getScheduledEntityDeletions');

        $this->listener->onFlush($this->getEventArgs());
    }

    public function testOnFlushWhenHasCacheAndHasDeletedWebsite()
    {
        $this->cacheableProvider->expects($this->once())
            ->method('hasCache')
            ->willReturn(true);
        $this->cacheableProvider->expects($this->once())
            ->method('clearCache');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([new \stdClass()]);
        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([new \stdClass()]);
        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([$this->createMock(Website::class)]);

        $this->listener->onFlush($this->getEventArgs());
    }

    /**
     * @return OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEventArgs()
    {
        /** @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->createMock(OnFlushEventArgs::class);

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($em);

        return $args;
    }
}
