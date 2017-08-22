<?php

namespace Oro\Bundle\CustomerBundle\Tests\Event;

use Oro\Bundle\CustomerBundle\Event\BeforeCustomerUserRegisterEvent;

class BeforeCustomerUserRegisterEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BeforeCustomerUserRegisterEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->event = new BeforeCustomerUserRegisterEvent();
    }

    public function testGetRedirect()
    {
        $this->event->setRedirect(['route' => 'some_route']);
        $this->assertSame(['route' => 'some_route'], $this->event->getRedirect());
    }
}
