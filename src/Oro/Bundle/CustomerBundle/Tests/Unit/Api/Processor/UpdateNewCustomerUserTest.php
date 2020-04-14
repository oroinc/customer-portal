<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\ApiBundle\Tests\Unit\Processor\CustomizeFormData\CustomizeFormDataProcessorTestCase;
use Oro\Bundle\CustomerBundle\Api\Processor\UpdateNewCustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Symfony\Component\Form\FormInterface;

class UpdateNewCustomerUserTest extends CustomizeFormDataProcessorTestCase
{
    /** @var CustomerUserManager|\PHPUnit\Framework\MockObject\MockObject */
    private $userManager;

    /** @var UpdateNewCustomerUser */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userManager = $this->createMock(CustomerUserManager::class);

        $this->context->setEvent(CustomizeFormDataContext::EVENT_POST_VALIDATE);
        $this->context->setForm($this->createMock(FormInterface::class));

        $this->processor = new UpdateNewCustomerUser($this->userManager);
    }

    public function testProcessWhenFormIsNotValid()
    {
        /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject $form */
        $form = $this->context->getForm();
        $form->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(false);
        $form->expects(self::never())
            ->method('getData');

        $this->userManager->expects(self::never())
            ->method('updateUser');

        $this->processor->process($this->context);
    }

    public function testProcessWhenUserDoesNotHavePassword()
    {
        $user = $this->createMock(CustomerUser::class);
        $plainPassword = 'some_password';

        /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject $form */
        $form = $this->context->getForm();
        $form->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $form->expects(self::once())
            ->method('getData')
            ->willReturn($user);

        $user->expects(self::once())
            ->method('getPlainPassword')
            ->willReturn(null);
        $user->expects(self::once())
            ->method('setPlainPassword')
            ->with($plainPassword);

        $this->userManager->expects(self::once())
            ->method('generatePassword')
            ->willReturn($plainPassword);
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with(self::identicalTo($user), self::isFalse());

        $this->processor->process($this->context);
    }

    public function testProcessWhenUserPasswordAlreadySet()
    {
        $user = $this->createMock(CustomerUser::class);

        /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject $form */
        $form = $this->context->getForm();
        $form->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $form->expects(self::once())
            ->method('getData')
            ->willReturn($user);

        $user->expects(self::once())
            ->method('getPlainPassword')
            ->willReturn('test_password');
        $user->expects(self::never())
            ->method('setPlainPassword');

        $this->userManager->expects(self::never())
            ->method('generatePassword');
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with(self::identicalTo($user), self::isFalse());

        $this->processor->process($this->context);
    }
}
