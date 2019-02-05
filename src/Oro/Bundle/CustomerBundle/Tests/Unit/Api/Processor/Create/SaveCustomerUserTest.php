<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\Processor\Create;

use Oro\Bundle\ApiBundle\Tests\Unit\Processor\FormProcessorTestCase;
use Oro\Bundle\CustomerBundle\Api\Processor\Create\SaveCustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;

class SaveCustomerUserTest extends FormProcessorTestCase
{
    /** @var CustomerUserManager|\PHPUnit\Framework\MockObject\MockObject */
    private $userManager;

    /** @var SaveCustomerUser */
    private $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->userManager = $this->createMock(CustomerUserManager::class);

        $this->processor = new SaveCustomerUser($this->userManager);
    }

    public function testProcessWhenNoResult()
    {
        $this->userManager->expects(self::never())
            ->method('updateUser');

        $this->processor->process($this->context);
    }

    public function testProcessWhenResultIsNotObject()
    {
        $user = [];

        $this->userManager->expects(self::never())
            ->method('updateUser');

        $this->context->setResult($user);
        $this->processor->process($this->context);
    }

    public function testProcessWhenUserDoesNotHavePassword()
    {
        $user = $this->createMock(CustomerUser::class);
        $plainPassword = 'some_password';

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
            ->with(self::identicalTo($user));

        $this->context->setResult($user);
        $this->processor->process($this->context);
    }

    public function testProcessWhenUserPasswordAlreadySet()
    {
        $user = $this->createMock(CustomerUser::class);

        $user->expects(self::once())
            ->method('getPlainPassword')
            ->willReturn('test_password');
        $user->expects(self::never())
            ->method('setPlainPassword');

        $this->userManager->expects(self::never())
            ->method('generatePassword');
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with(self::identicalTo($user));

        $this->context->setResult($user);
        $this->processor->process($this->context);
    }
}
