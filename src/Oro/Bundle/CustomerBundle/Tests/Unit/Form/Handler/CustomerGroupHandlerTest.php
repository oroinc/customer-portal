<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Event\CustomerGroupEvent;
use Oro\Bundle\CustomerBundle\Event\CustomerMassEvent;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerGroupHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FormInterface
     */
    protected $form;

    /**
     * @var CustomerGroupHandler
     */
    protected $handler;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ObjectManager
     */
    protected $manager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var CustomerGroup
     */
    protected $entity;

    protected function setUp(): void
    {
        $this->manager = $this->getMockBuilder('Doctrine\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = new Request();
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity  = new CustomerGroup();
        $this->handler = new CustomerGroupHandler($this->form, $this->request, $this->manager, $this->dispatcher);
    }

    public function testProcessValidData()
    {
        $appendedCustomer = new Customer();

        $removedCustomer = new Customer();
        $removedCustomer->setGroup($this->entity);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('handleRequest')
            ->with($this->request);

        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $appendForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $appendForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([$appendedCustomer]));
        $this->form->expects($this->at(4))
            ->method('get')
            ->with('appendCustomers')
            ->will($this->returnValue($appendForm));

        $removeForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $removeForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([$removedCustomer]));
        $this->form->expects($this->at(5))
            ->method('get')
            ->with('removeCustomers')
            ->will($this->returnValue($removeForm));

        $this->manager->expects($this->at(0))
            ->method('persist')
            ->with($appendedCustomer);

        $this->manager->expects($this->at(1))
            ->method('persist')
            ->with($removedCustomer);

        $this->manager->expects($this->at(2))
            ->method('persist')
            ->with($this->entity);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    new CustomerGroupEvent($this->entity, $this->form),
                    CustomerGroupEvent::BEFORE_FLUSH
                ],
                [
                    new CustomerMassEvent([$appendedCustomer, $removedCustomer]),
                    CustomerMassEvent::ON_CUSTOMER_GROUP_MASS_CHANGE
                ]
            );

        $this->assertTrue($this->handler->process($this->entity));

        $this->assertEquals($this->entity, $appendedCustomer->getGroup());
        $this->assertNull($removedCustomer->getGroup());
    }

    public function testBadMethod()
    {
        $this->request->setMethod('GET');
        $this->assertFalse($this->handler->process($this->entity));
    }

    public function testProcessInvalid()
    {
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);
        $this->form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->assertFalse($this->handler->process($this->entity));
    }
}
