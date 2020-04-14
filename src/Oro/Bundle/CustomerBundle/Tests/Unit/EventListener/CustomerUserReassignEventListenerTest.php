<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\CustomerUserReassignEventListener;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignUpdaterInterface;

class CustomerUserReassignEventListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserReassignEventListener */
    private $listener;

    /** @var CustomerUserReassignUpdaterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserReassignUpdater;

    /** @var PreUpdateEventArgs|\PHPUnit\Framework\MockObject\MockObject */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->customerUserReassignUpdater = $this->createMock(CustomerUserReassignUpdaterInterface::class);
        $this->listener = new CustomerUserReassignEventListener($this->customerUserReassignUpdater);
        $this->event = $this->createMock(PreUpdateEventArgs::class);
    }

    public function testPreUpdate()
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

    public function testPreUpdateCustomerFieldNotChanged()
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
