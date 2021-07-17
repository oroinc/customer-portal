<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\EventListener\WebsiteListener;
use Oro\Bundle\WebsiteBundle\Provider\CacheableWebsiteProvider;

class WebsiteListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CacheableWebsiteProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $cacheableProvider;

    /** @var WebsiteListener */
    private $listener;

    /** @var UnitOfWork|\PHPUnit\Framework\MockObject\MockObject */
    private $uow;

    protected function setUp(): void
    {
        $this->cacheableProvider = $this->createMock(CacheableWebsiteProvider::class);

        $this->listener = new WebsiteListener($this->cacheableProvider);

        $this->uow = $this->createMock(UnitOfWork::class);
    }

    public function testOnFlushWhenNoScheduledWebsite()
    {
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

    public function testOnFlushWhenHasInsertedWebsite()
    {
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

    public function testOnFlushWhenHasUpdatedWebsite()
    {
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

    public function testOnFlushWhenHasDeletedWebsite()
    {
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
     * @return OnFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getEventArgs()
    {
        /** @var OnFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $args */
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
