<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Event\CustomerGroupEvent;
use Oro\Bundle\CustomerBundle\Event\CustomerMassEvent;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerGroupHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var Request */
    private $request;

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var CustomerGroupHandler */
    private $handler;

    /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject */
    private $manager;

    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $dispatcher;

    /** @var CustomerGroup */
    private $entity;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(ObjectManager::class);
        $this->request = new Request();
        $this->form = $this->createMock(Form::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->entity  = new CustomerGroup();

        $this->handler = new CustomerGroupHandler($this->manager, $this->dispatcher);
    }

    public function testProcessValidData(): void
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
            ->willReturn(true);
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $appendForm = $this->createMock(Form::class);
        $appendForm->expects($this->once())
            ->method('getData')
            ->willReturn([$appendedCustomer]);

        $removeForm = $this->createMock(Form::class);
        $removeForm->expects($this->once())
            ->method('getData')
            ->willReturn([$removedCustomer]);

        $this->form->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['appendCustomers', $appendForm],
                ['removeCustomers', $removeForm]
            ]);

        $this->manager->expects($this->exactly(3))
            ->method('persist')
            ->withConsecutive(
                [$this->identicalTo($appendedCustomer)],
                [$this->identicalTo($removedCustomer)],
                [$this->identicalTo($this->entity)]
            );
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

        $this->assertTrue($this->handler->process($this->entity, $this->form, $this->request));

        $this->assertEquals($this->entity, $appendedCustomer->getGroup());
        $this->assertNull($removedCustomer->getGroup());
    }

    public function testBadMethod(): void
    {
        $this->request->setMethod('GET');
        $this->assertFalse($this->handler->process($this->entity, $this->form, $this->request));
    }

    public function testProcessInvalid(): void
    {
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);
        $this->form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->assertFalse($this->handler->process($this->entity, $this->form, $this->request));
    }
}
