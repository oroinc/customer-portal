<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Event\CustomerUserRegisterEvent;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\FormHandlerTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler;
use Oro\Bundle\WebsiteBundle\Entity\Website;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FrontendCustomerUserHandlerTest extends FormHandlerTestCase
{
    use EntityTrait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CustomerUserManager
     */
    protected $userManager;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * @var CustomerUser
     */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        #$this->entity = $this->getEntity(CustomerUser::class, ['id' => 2]);
        $this->entity = new CustomerUser();
        $this->userManager = $this->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\CustomerUserManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->handler = new FrontendCustomerUserHandler(
            $this->form,
            $this->request,
            $this->userManager,
            $this->eventDispatcher
        );
    }

    public function testProcessUnsupportedRequest()
    {
        $this->request->setMethod('GET');

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * {@inheritdoc}
     * @dataProvider supportedMethods
     */
    public function testProcessSupportedRequest($method, $isValid, $isProcessed)
    {
        $this->form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue($isValid));

        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->assertEquals($isProcessed, $this->handler->process($this->entity));
    }

    /**
     * {@inheritdoc}
     */
    public function testProcessValidData()
    {
        $this->request->setMethod('POST');

        $website = new Website();
        $this->request->attributes->set('current_website', $website);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->userManager->expects($this->once())
            ->method('register')
            ->with($this->entity);

        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($this->entity);

        $this->userManager->expects($this->never())
            ->method('reloadUser')
            ->with($this->entity);

        $event = new CustomerUserRegisterEvent($this->entity);
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(CustomerUserRegisterEvent::NAME, $event);

        $this->assertTrue($this->handler->process($this->entity));
    }

    /**
     * {@inheritdoc}
     */
    public function testProcessValidDataExistingUser()
    {
        $this->entity = $this->getEntity(CustomerUser::class, ['id' => 42]);
        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->userManager->expects($this->never())
            ->method('register')
            ->with($this->entity);

        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($this->entity);

        $this->userManager->expects($this->once())
            ->method('reloadUser')
            ->with($this->entity);

        $this->assertTrue($this->handler->process($this->entity));
    }
}
