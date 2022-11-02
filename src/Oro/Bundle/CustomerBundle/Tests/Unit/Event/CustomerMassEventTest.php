<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Event;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Event\CustomerMassEvent;

class CustomerMassEventTest extends \PHPUnit\Framework\TestCase
{
    public function testAccessors()
    {
        $customer = new Customer();
        $event = new CustomerMassEvent([$customer]);
        $this->assertSame([$customer], $event->getCustomers());
    }
}
