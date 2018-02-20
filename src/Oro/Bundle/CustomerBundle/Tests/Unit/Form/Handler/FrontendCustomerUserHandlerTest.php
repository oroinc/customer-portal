<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\FormHandlerTestCase;

class FrontendCustomerUserHandlerTest extends FormHandlerTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CustomerUserManager
     */
    protected $userManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->entity = $this->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\CustomerUser')
            ->disableOriginalConstructor()
            ->getMock();

        $this->userManager = $this->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\CustomerUserManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new FrontendCustomerUserHandler(
            $this->form,
            $this->request,
            $this->userManager
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
        $this->entity->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue(null));
        $this->request->setMethod('POST');

        $website = new Website();
        $this->request->attributes->set('current_website', $website);
        $this->entity->expects($this->once())
            ->method('setWebsite')
            ->with($website);

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

        $this->assertTrue($this->handler->process($this->entity));
    }

    /**
     * {@inheritdoc}
     */
    public function testProcessValidDataExistingUser()
    {
        $this->entity->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue(42));
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
