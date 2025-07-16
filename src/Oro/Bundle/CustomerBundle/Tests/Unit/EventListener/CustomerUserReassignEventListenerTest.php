<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\CustomerUserReassignEventListener;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerUserReassignEventListenerTest extends TestCase
{
    private CustomerUserReassignEventListener $listener;
    private CustomerUserReassignUpdaterInterface&MockObject $customerUserReassignUpdater;
    private PreUpdateEventArgs&MockObject $event;

    #[\Override]
    protected function setUp(): void
    {
        $this->customerUserReassignUpdater = $this->createMock(CustomerUserReassignUpdaterInterface::class);
        $this->listener = new CustomerUserReassignEventListener($this->customerUserReassignUpdater);
        $this->event = $this->createMock(PreUpdateEventArgs::class);
    }

    public function testPreUpdate(): void
    {
        $customerUser = new CustomerUser();

        $this->event->expects(self::once())
            ->method('hasChangedField')
            ->with('customer')
            ->willReturn(true);

        $this->customerUserReassignUpdater->expects(self::once())
            ->method('update')
            ->with($customerUser);

        $this->listener->preUpdate($customerUser, $this->event);
    }

    public function testPreUpdateCustomerFieldNotChanged(): void
    {
        $customerUser = new CustomerUser();

        $this->event->expects(self::once())
            ->method('hasChangedField')
            ->with('customer')
            ->willReturn(false);

        $this->customerUserReassignUpdater->expects(self::never())
            ->method('update');

        $this->listener->preUpdate($customerUser, $this->event);
    }
}
