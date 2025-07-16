<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Listener\CustomerUserDoctrineAclCacheListener;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Cache\DoctrineAclCacheProvider;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerUserDoctrineAclCacheListenerTest extends TestCase
{
    private DoctrineAclCacheProvider&MockObject $queryCacheProvider;
    private OwnerTreeProviderInterface&MockObject $ownerTreeProvider;
    private CustomerUserDoctrineAclCacheListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->queryCacheProvider = $this->createMock(DoctrineAclCacheProvider::class);
        $this->ownerTreeProvider = $this->createMock(OwnerTreeProviderInterface::class);

        $this->listener = new CustomerUserDoctrineAclCacheListener(
            $this->queryCacheProvider,
            $this->ownerTreeProvider
        );
        $this->listener->addEntityShouldBeProcessedByUpdate(Organization::class, ['enabled' => true]);
    }

    public function testOwnerTreeShouldNotBeTriggeredForUpdateNonOrganizationEntity(): void
    {
        $uow = $this->createMock(UnitOfWork::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $entity = new CustomerUser();

        $em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);
        $uow->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([$entity]);
        $uow->expects(self::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);
        $uow->expects(self::once())
            ->method('getScheduledCollectionUpdates')
            ->willReturn([]);
        $uow->expects(self::once())
            ->method('getEntityChangeSet')
            ->with($entity)
            ->willReturn([]);

        $this->ownerTreeProvider->expects(self::never())
            ->method('getTree');

        $this->listener->onFlush(new OnFlushEventArgs($em));
    }

    public function testOwnerTreeShouldBeTriggeredForUpdateOrganizationEntity(): void
    {
        $uow = $this->createMock(UnitOfWork::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $entity = new Organization();

        $em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);
        $uow->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([$entity]);
        $uow->expects(self::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);
        $uow->expects(self::once())
            ->method('getScheduledCollectionUpdates')
            ->willReturn([]);
        $uow->expects(self::once())
            ->method('getEntityChangeSet')
            ->with($entity)
            ->willReturn(['enabled' => [1, 0]]);

        $this->ownerTreeProvider->expects(self::once())
            ->method('getTree')
            ->willReturn(new OwnerTree());

        $this->listener->onFlush(new OnFlushEventArgs($em));
    }

    public function testOwnerTreeShouldNotBeTriggeredForDeleteNonCustomerEntity(): void
    {
        $uow = $this->createMock(UnitOfWork::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $entity = new CustomerUser();

        $em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);
        $uow->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);
        $uow->expects(self::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([$entity]);
        $uow->expects(self::once())
            ->method('getScheduledCollectionUpdates')
            ->willReturn([]);

        $this->ownerTreeProvider->expects(self::never())
            ->method('getTree');

        $this->listener->onFlush(new OnFlushEventArgs($em));
    }

    public function testOwnerTreeShouldBeTriggeredForDeleteCustomerEntity(): void
    {
        $uow = $this->createMock(UnitOfWork::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $entity = new Customer();

        $em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);
        $uow->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);
        $uow->expects(self::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([$entity]);
        $uow->expects(self::once())
            ->method('getScheduledCollectionUpdates')
            ->willReturn([]);

        $this->ownerTreeProvider->expects(self::once())
            ->method('getTree')
            ->willReturn(new OwnerTree());

        $this->listener->onFlush(new OnFlushEventArgs($em));
    }
}
