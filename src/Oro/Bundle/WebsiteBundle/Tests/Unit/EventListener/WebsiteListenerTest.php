<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\EventListener\WebsiteListener;
use Oro\Bundle\WebsiteBundle\Provider\CacheableWebsiteProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteListenerTest extends TestCase
{
    private CacheableWebsiteProvider&MockObject $cacheableProvider;
    private UnitOfWork&MockObject $uow;
    private WebsiteListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->cacheableProvider = $this->createMock(CacheableWebsiteProvider::class);
        $this->uow = $this->createMock(UnitOfWork::class);

        $this->listener = new WebsiteListener($this->cacheableProvider);
    }

    public function testOnFlushWhenNoScheduledWebsite(): void
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

    public function testOnFlushWhenHasInsertedWebsite(): void
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

    public function testOnFlushWhenHasUpdatedWebsite(): void
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

    public function testOnFlushWhenHasDeletedWebsite(): void
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

    private function getEventArgs(): OnFlushEventArgs
    {
        $args = $this->createMock(OnFlushEventArgs::class);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $args->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($em);

        return $args;
    }
}
