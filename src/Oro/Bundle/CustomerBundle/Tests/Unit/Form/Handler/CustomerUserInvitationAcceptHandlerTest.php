<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserInvitationAcceptHandler;
use Oro\Bundle\CustomerBundle\Manager\CustomerUserInvitationManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerUserInvitationAcceptHandlerTest extends TestCase
{
    private const TOKEN = 'invitation-token';
    private const FORM_DATA = ['firstName' => 'Invited'];

    private CustomerUserInvitationManager&MockObject $invitationManager;
    private FormInterface&MockObject $form;
    private CustomerUserInvitation $invitation;
    private CustomerUser $customerUser;
    private CustomerUserInvitationAcceptHandler $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->invitationManager = $this->createMock(CustomerUserInvitationManager::class);
        $this->form = $this->createMock(FormInterface::class);
        $this->form->method('getName')->willReturn('accept');
        $this->invitation = new CustomerUserInvitation();
        $this->customerUser = new CustomerUser();
        $this->handler = new CustomerUserInvitationAcceptHandler(
            $this->invitationManager,
            $this->invitation,
            self::TOKEN
        );
    }

    public function testGetRequestDoesNotSubmitForm(): void
    {
        $this->form->expects(self::never())->method('submit');
        $this->invitationManager->expects(self::never())->method('accept');

        self::assertFalse($this->handler->process($this->customerUser, $this->form, $this->createRequest('GET')));
    }

    public function testInvalidFormDoesNotAcceptInvitation(): void
    {
        $this->expectFormSubmission(false);
        $this->invitationManager->expects(self::never())->method('accept');

        self::assertFalse($this->handler->process($this->customerUser, $this->form, $this->createRequest()));
    }

    public function testValidFormAcceptsInvitation(): void
    {
        $this->expectFormSubmission(true);
        $this->invitationManager->expects(self::once())
            ->method('accept')
            ->with($this->invitation, $this->customerUser, self::TOKEN)
            ->willReturn($this->customerUser);

        self::assertTrue($this->handler->process($this->customerUser, $this->form, $this->createRequest()));
    }

    public function testInvalidTokenErrorIsPropagated(): void
    {
        $this->expectFormSubmission(true);
        $this->invitationManager->method('accept')->willThrowException(new \LogicException('Invalid invitation'));

        $this->expectException(\LogicException::class);
        $this->handler->process($this->customerUser, $this->form, $this->createRequest());
    }

    private function createRequest(string $method = 'POST'): Request
    {
        $request = new Request([], ['accept' => self::FORM_DATA]);
        $request->setMethod($method);

        return $request;
    }

    private function expectFormSubmission(bool $valid): void
    {
        $this->form->expects(self::once())->method('submit')->with(self::FORM_DATA, true);
        $this->form->expects(self::once())->method('isValid')->willReturn($valid);
    }
}
