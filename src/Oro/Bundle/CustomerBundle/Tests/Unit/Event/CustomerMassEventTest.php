<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Event;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Event\CustomerMassEvent;
use PHPUnit\Framework\TestCase;

class CustomerMassEventTest extends TestCase
{
    public function testAccessors(): void
    {
        $customer = new Customer();
        $event = new CustomerMassEvent([$customer]);
        $this->assertSame([$customer], $event->getCustomers());
    }
}
