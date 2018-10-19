<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordRequestHandler;

class CustomerUserPasswordRequestHandlerTest extends AbstractCustomerUserPasswordHandlerTestCase
{
    /**
     * @var CustomerUserPasswordRequestHandler
     */
    protected $handler;

    protected function setUp()
    {
        parent::setUp();

        $this->handler = new CustomerUserPasswordRequestHandler($this->userManager, $this->translator);
    }

    public function testProcessInvalidUser()
    {
        $email = 'test@test.com';
        $emailSubform = $this->assertValidFormCall($email);

        $this->userManager->expects($this->once())
            ->method('findUserByUsernameOrEmail')
            ->with($email);

        $this->assertFormErrorAdded(
            $emailSubform,
            'oro.customer.customeruser.profile.email_not_exists',
            ['%email%' => $email]
        );

        $this->assertFalse($this->handler->process($this->form, $this->request));
    }

    public function testProcessEmailSendFail()
    {
        $email = 'test@test.com';
        $token = 'answerisfourtytwo';

        $user = $this->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\CustomerUser')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getConfirmationToken')
            ->will($this->returnValue($token));
        $user->expects($this->never())
            ->method('generateToken');
        $user->expects($this->never())
            ->method('setConfirmationToken');

        $this->assertValidFormCall($email);

        $this->userManager->expects($this->once())
            ->method('findUserByUsernameOrEmail')
            ->with($email)
            ->will($this->returnValue($user));

        $this->userManager->expects($this->once())
            ->method('sendResetPasswordEmail')
            ->with($user)
            ->will($this->throwException(new \Exception()));

        $this->assertFormErrorAdded(
            $this->form,
            'oro.email.handler.unable_to_send_email'
        );

        $this->assertFalse($this->handler->process($this->form, $this->request));
    }

    public function testProcess()
    {
        $email = 'test@test.com';
        $token = 'answerisfourtytwo';

        $user = $this->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\CustomerUser')
            ->disableOriginalConstructor()
            ->getMock();

        $user->expects($this->once())
            ->method('getConfirmationToken')
            ->will($this->returnValue(null));
        $user->expects($this->once())
            ->method('generateToken')
            ->will($this->returnValue($token));
        $user->expects($this->once())
            ->method('setConfirmationToken')
            ->with($token);
        $user->expects($this->once())
            ->method('setPasswordRequestedAt')
            ->with($this->isInstanceOf('\DateTime'));

        $this->assertValidFormCall($email);

        $this->userManager->expects($this->once())
            ->method('findUserByUsernameOrEmail')
            ->with($email)
            ->will($this->returnValue($user));

        $this->userManager->expects($this->once())
            ->method('sendResetPasswordEmail')
            ->with($user);

        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($user);

        $this->assertEquals($user, $this->handler->process($this->form, $this->request));
    }

    /**
     * @param string $email
     */
    protected function assertValidFormCall($email)
    {
        parent::assertValidForm();

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

        $emailSubform = $this->createMock('Symfony\Component\Form\FormInterface');
        $emailSubform->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($email));

        $this->form->expects($this->once())
            ->method('get')
            ->with('email')
            ->will($this->returnValue($emailSubform));

        return $emailSubform;
    }
}
