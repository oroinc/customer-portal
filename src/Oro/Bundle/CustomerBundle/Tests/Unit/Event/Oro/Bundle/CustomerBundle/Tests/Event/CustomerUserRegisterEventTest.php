<?php

namespace Oro\Bundle\CustomerBundle\Tests\Event;

use Oro\Bundle\CustomerBundle\Event\CustomerUserRegisterEvent;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

class CustomerUserRegisterEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerUserRegisterEvent
     */
    private $event;

    /**
     * @var CustomerUser
     */
    private $customerUser;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->customerUser = new CustomerUser();
        $this->event = new CustomerUserRegisterEvent($this->customerUser);
    }

    public function testGetCustomerUser()
    {
        $this->assertSame($this->customerUser, $this->event->getCustomerUser());
    }
}
