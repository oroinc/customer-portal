<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Form\Handler\AbstractCustomerUserPasswordHandler;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

abstract class AbstractCustomerUserPasswordHandlerTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $userManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $translator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $form;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var AbstractCustomerUserPasswordHandler
     */
    protected $handler;

    protected function setUp()
    {
        $this->userManager = $this->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\CustomerUserManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');

        $this->form = $this->createMock('Symfony\Component\Form\FormInterface');
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->handler, $this->userManager, $this->translator, $this->form, $this->request);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|FormInterface $form
     * @param string $message
     * @param array $messageParameters
     */
    public function assertFormErrorAdded($form, $message, array $messageParameters = [])
    {
        $this->translator->expects($this->once())
            ->method('trans')
            ->with($message, $messageParameters)
            ->will($this->returnValue($message));

        $form->expects($this->once())
            ->method('addError')
            ->with(new FormError($message));
    }

    public function testProcessUnsupportedMethod()
    {
        $this->request->expects($this->once())
            ->method('isMethod')
            ->with('POST')
            ->will($this->returnValue(false));
        $this->form->expects($this->never())
            ->method('handleRequest');

        $this->assertFalse($this->handler->process($this->form, $this->request));
    }

    public function testProcessInvalidForm()
    {
        $this->request->expects($this->once())
            ->method('isMethod')
            ->with('POST')
            ->will($this->returnValue(true));
        $this->form->expects($this->once())
            ->method('handleRequest')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->assertFalse($this->handler->process($this->form, $this->request));
    }

    protected function assertValidForm()
    {
        $this->request->expects($this->once())
            ->method('isMethod')
            ->with('POST')
            ->will($this->returnValue(true));
        $this->form->expects($this->once())
            ->method('handleRequest')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
    }
}
