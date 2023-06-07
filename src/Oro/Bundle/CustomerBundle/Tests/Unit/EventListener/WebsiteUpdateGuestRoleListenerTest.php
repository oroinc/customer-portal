<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\CustomerBundle\Acl\Cache\CustomerVisitorAclCache;
use Oro\Bundle\CustomerBundle\EventListener\WebsiteUpdateGuestRoleListener;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;

class WebsiteUpdateGuestRoleListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject|CustomerVisitorAclCache */
    private $visitorAclCache;

    /** @var \PHPUnit\Framework\MockObject\MockObject|UnitOfWork */
    private $uow;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager */
    private $em;

    /** @var WebsiteUpdateGuestRoleListener */
    private $listener;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->uow = $this->createMock(UnitOfWork::class);

        $this->visitorAclCache = $this->createMock(CustomerVisitorAclCache::class);
        $this->listener = new WebsiteUpdateGuestRoleListener($this->visitorAclCache);

        $this->em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);
    }

    public function testPostUpdateWithoutChangesOnGuestRoleField(): void
    {
        $website = new Website();
        $event = new LifecycleEventArgs($website, $this->em);

        $this->uow->expects(self::once())
            ->method('getEntityChangeSet')
            ->willReturn(['name' => ['new', 'old']]);

        $this->visitorAclCache->expects(self::never())
            ->method('clearWebsiteData');

        $this->listener->postUpdate($website, $event);
    }

    public function testPostUpdateWhenChangesOnGuestRoleFieldHaveSameValue(): void
    {
        $website = new Website();
        $event = new LifecycleEventArgs($website, $this->em);

        $this->uow->expects(self::once())
            ->method('getEntityChangeSet')
            ->willReturn(['guest_role' => ['old', 'old']]);

        $this->visitorAclCache->expects(self::never())
            ->method('clearWebsiteData');

        $this->listener->postUpdate($website, $event);
    }

    public function testPostUpdateWithChanges(): void
    {
        $website = $this->getEntity(Website::class, ['id' => 24]);
        $event = new LifecycleEventArgs($website, $this->em);

        $this->uow->expects(self::once())
            ->method('getEntityChangeSet')
            ->willReturn(['guest_role' => ['new', 'old']]);

        $this->visitorAclCache->expects(self::once())
            ->method('clearWebsiteData')
            ->with(24);

        $this->listener->postUpdate($website, $event);
    }
}
