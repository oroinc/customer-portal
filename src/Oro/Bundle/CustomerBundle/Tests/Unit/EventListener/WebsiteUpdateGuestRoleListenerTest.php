<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\CustomerBundle\Acl\Cache\CustomerVisitorAclCache;
use Oro\Bundle\CustomerBundle\EventListener\WebsiteUpdateGuestRoleListener;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteUpdateGuestRoleListenerTest extends TestCase
{
    use EntityTrait;

    private CustomerVisitorAclCache&MockObject $visitorAclCache;
    private UnitOfWork&MockObject $uow;
    private EntityManagerInterface&MockObject $em;
    private WebsiteUpdateGuestRoleListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
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
        $event = new PostUpdateEventArgs($website, $this->em);

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
        $event = new PostUpdateEventArgs($website, $this->em);

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
        $event = new PostUpdateEventArgs($website, $this->em);

        $this->uow->expects(self::once())
            ->method('getEntityChangeSet')
            ->willReturn(['guest_role' => ['new', 'old']]);

        $this->visitorAclCache->expects(self::once())
            ->method('clearWebsiteData')
            ->with(24);

        $this->listener->postUpdate($website, $event);
    }
}
