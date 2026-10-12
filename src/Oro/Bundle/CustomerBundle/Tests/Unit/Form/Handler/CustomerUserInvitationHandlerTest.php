<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserInvitationHandler;
use Oro\Bundle\CustomerBundle\Manager\CustomerUserInvitationManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerUserInvitationHandlerTest extends TestCase
{
    private const FORM_DATA = ['email' => 'invite@example.org'];

    private CustomerUserInvitationManager&MockObject $invitationManager;
    private TranslatorInterface&MockObject $translator;
    private LoggerInterface&MockObject $logger;
    private FormInterface&MockObject $form;
    private CustomerUserInvitation $invitation;

    #[\Override]
    protected function setUp(): void
    {
        $this->invitationManager = $this->createMock(CustomerUserInvitationManager::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnCallback(static fn (string $key): string => $key);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->form = $this->createMock(FormInterface::class);
        $this->form->method('getName')->willReturn('invitation');
        $this->invitation = new CustomerUserInvitation();
    }

    public function testGetRequestDoesNotSubmitForm(): void
    {
        $this->form->expects(self::never())->method('submit');
        $this->invitationManager->expects(self::never())->method('invite');

        $handler = $this->createHandler();

        self::assertFalse($handler->process($this->invitation, $this->form, $this->createRequest('GET')));
    }

    public function testInvalidFormDoesNotSendInvitation(): void
    {
        $this->expectFormSubmission(false);
        $this->invitationManager->expects(self::never())->method('invite');
        $request = $this->createRequest();

        self::assertFalse($this->createHandler()->process($this->invitation, $this->form, $request));
        self::assertEquals([], $request->getSession()->getFlashBag()->all());
    }

    public function testValidFormSendsInvitation(): void
    {
        $this->expectFormSubmission(true);
        $this->invitationManager->expects(self::once())
            ->method('invite')
            ->with($this->invitation);
        $request = $this->createRequest();

        self::assertTrue($this->createHandler()->process($this->invitation, $this->form, $request));
        self::assertEquals(
            ['success' => ['oro.customer.customeruserinvitation.message.sent']],
            $request->getSession()->getFlashBag()->all()
        );
    }

    public function testDomainErrorIsAddedToEmail(): void
    {
        $this->expectFormSubmission(true);
        $emailField = $this->createMock(FormInterface::class);
        $emailField->expects(self::once())
            ->method('addError')
            ->with(self::callback(static fn (FormError $error): bool =>
                $error->getMessage() === 'oro.customer.customeruserinvitation.error.cannot_invite'));
        $this->form->expects(self::once())->method('get')->with('email')->willReturn($emailField);
        $exception = new \DomainException();
        $this->invitationManager->method('invite')->willThrowException($exception);
        $this->logger->expects(self::once())
            ->method('warning')
            ->with(
                'Customer user invitation could not be created.',
                ['exception' => $exception, 'customer_id' => null]
            );
        $request = $this->createRequest();

        self::assertFalse($this->createHandler()->process($this->invitation, $this->form, $request));
        self::assertEquals([], $request->getSession()->getFlashBag()->all());
    }

    public function testEmailFailureShowsWarningAfterInvitationWasSaved(): void
    {
        $this->expectFormSubmission(true);
        $exception = new \RuntimeException();
        $this->invitationManager->method('invite')->willThrowException($exception);
        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                'Customer user invitation could not be sent.',
                ['exception' => $exception, 'invitation_id' => null]
            );
        $request = $this->createRequest();

        self::assertTrue($this->createHandler()->process($this->invitation, $this->form, $request));
        self::assertEquals(
            ['warning' => ['oro.customer.customeruserinvitation.message.saved_email_failed']],
            $request->getSession()->getFlashBag()->all()
        );
    }

    public function testBackOfficeFormUsesInviterScope(): void
    {
        $this->expectFormSubmission(true);
        $customer = $this->createCustomer(1);
        $organization = new Organization();
        $website = new Website();
        $inviter = (new CustomerUser())
            ->setCustomer($customer)
            ->setOrganization($organization)
            ->setWebsite($website);
        $this->invitation->setCustomer($customer)->setInvitedBy($inviter);
        $this->invitationManager->expects(self::once())->method('invite')->with($this->invitation);

        self::assertTrue($this->createHandler(true)->process($this->invitation, $this->form, $this->createRequest()));
        self::assertEquals($organization, $this->invitation->getOrganization());
        self::assertEquals($website, $this->invitation->getWebsite());
    }

    public function testBackOfficeFormRejectsInviterFromAnotherCustomer(): void
    {
        $this->expectFormSubmission(false);
        $this->invitation->setCustomer($this->createCustomer(1));
        $this->invitation->setInvitedBy((new CustomerUser())->setCustomer($this->createCustomer(2)));
        $this->expectInviterError('oro.customer.customeruserinvitation.error.inviter_customer_mismatch');
        $this->invitationManager->expects(self::never())->method('invite');

        self::assertFalse($this->createHandler(true)->process($this->invitation, $this->form, $this->createRequest()));
    }

    public function testBackOfficeFormRejectsInviterWithoutOrganizationAndWebsite(): void
    {
        $this->expectFormSubmission(false);
        $customer = $this->createCustomer(1);
        $this->invitation->setCustomer($customer);
        $this->invitation->setInvitedBy((new CustomerUser())->setCustomer($customer));
        $this->expectInviterError('oro.customer.customeruserinvitation.error.invalid_inviter');
        $this->invitationManager->expects(self::never())->method('invite');

        self::assertFalse($this->createHandler(true)->process($this->invitation, $this->form, $this->createRequest()));
    }

    private function createHandler(bool $validateInviterScope = false): CustomerUserInvitationHandler
    {
        return new CustomerUserInvitationHandler(
            $this->invitationManager,
            $this->translator,
            $this->logger,
            $validateInviterScope
        );
    }

    private function createRequest(string $method = 'POST'): Request
    {
        $request = new Request([], ['invitation' => self::FORM_DATA]);
        $request->setMethod($method);
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    private function expectFormSubmission(bool $valid): void
    {
        $this->form->expects(self::once())->method('submit')->with(self::FORM_DATA, true);
        $this->form->expects(self::once())->method('isValid')->willReturn($valid);
    }

    private function expectInviterError(string $message): void
    {
        $field = $this->createMock(FormInterface::class);
        $field->expects(self::once())
            ->method('addError')
            ->with(self::callback(static fn (FormError $error): bool => $error->getMessage() === $message));
        $this->form->expects(self::once())->method('get')->with('invitedBy')->willReturn($field);
    }

    private function createCustomer(int $id): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);

        return $customer;
    }
}
