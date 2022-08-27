<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\ReflectionUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerUserHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const FORM_DATA = ['field' => 'value'];

    /** @var CustomerUserManager|\PHPUnit\Framework\MockObject\MockObject */
    private $userManager;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $passwordGenerateForm;

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $sendEmailForm;

    /** @var CustomerUser */
    private $entity;

    /** @var CustomerUserHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(CustomerUserManager::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->form = $this->createMock(Form::class);
        $this->passwordGenerateForm = $this->createMock(FormInterface::class);
        $this->sendEmailForm = $this->createMock(FormInterface::class);
        $this->entity = new CustomerUser();

        $this->handler = new CustomerUserHandler(
            $this->userManager,
            $this->tokenAccessor,
            $this->createMock(TranslatorInterface::class),
            $this->createMock(LoggerInterface::class)
        );
    }

    private function getCustomerUser(int $id): CustomerUser
    {
        $customerUser = new CustomerUser();
        ReflectionUtil::setId($customerUser, $id);

        return $customerUser;
    }

    private function getOrganization(string $name): Organization
    {
        $organization = new Organization();
        $organization->setName($name);

        return $organization;
    }

    public function testProcessUnsupportedRequest(): void
    {
        $request = new Request();
        $request->setMethod('GET');

        $this->form->expects(self::never())
            ->method('submit');

        self::assertFalse($this->handler->process($this->entity, $this->form, $request));
    }

    /**
     * @dataProvider supportedMethods
     */
    public function testProcessSupportedRequest(string $method): void
    {
        $request = new Request();
        $request->initialize([], self::FORM_DATA);
        $request->setMethod($method);

        $organization = $this->getOrganization('test');

        $this->tokenAccessor->expects(self::once())
            ->method('getOrganization')
            ->willReturn($organization);
        $this->userManager->expects(self::once())
            ->method('updateWebsiteSettings')
            ->with($this->entity);
        $this->form->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                ['passwordGenerate', $this->passwordGenerateForm],
                ['sendEmail', $this->sendEmailForm]
            ]);
        $this->passwordGenerateForm->expects(self::once())
            ->method('getData')
            ->willReturn(false);
        $this->sendEmailForm->expects(self::once())
            ->method('getData')
            ->willReturn(false);
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($this->entity);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $this->form->expects(self::once())
            ->method('submit')
            ->with(self::FORM_DATA);

        self::assertTrue($this->handler->process($this->entity, $this->form, $request));
        self::assertSame($organization, $this->entity->getOrganization());
    }

    public function supportedMethods(): array
    {
        return [['POST'], ['PUT']];
    }

    public function testProcessSupportedRequestWithInvalidData(): void
    {
        $request = new Request();
        $request->initialize([], self::FORM_DATA);
        $request->setMethod('POST');

        $this->userManager->expects(self::never())
            ->method('updateUser')
            ->with($this->entity);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(false);
        $this->form->expects(self::once())
            ->method('submit')
            ->with(self::FORM_DATA);

        self::assertFalse($this->handler->process($this->entity, $this->form, $request));
    }

    public function testProcessValidData(): void
    {
        $request = new Request();
        $request->initialize([], self::FORM_DATA);
        $request->setMethod('POST');

        $this->form->expects(self::once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->form->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                ['passwordGenerate', $this->passwordGenerateForm],
                ['sendEmail', $this->sendEmailForm]
            ]);

        $this->passwordGenerateForm->expects(self::once())
            ->method('getData')
            ->willReturn(true);
        $this->sendEmailForm->expects(self::once())
            ->method('getData')
            ->willReturn(true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        self::assertTrue($this->handler->process($this->entity, $this->form, $request));
    }

    public function testProcessCurrentUser(): void
    {
        $request = new Request();
        $request->initialize([], self::FORM_DATA);
        $request->setMethod('POST');

        $customerUser = $this->getCustomerUser(1);
        $organization = $this->getOrganization('test');

        $this->tokenAccessor->expects(self::once())
            ->method('getOrganization')
            ->willReturn($organization);
        $this->userManager->expects(self::never())
            ->method('sendWelcomeRegisteredByAdminEmail');
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $this->form->expects(self::once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->tokenAccessor->expects(self::once())
            ->method('getUserId')
            ->willReturn(1);
        $this->userManager->expects(self::once())
            ->method('reloadUser')
            ->with($customerUser);

        self::assertTrue($this->handler->process($customerUser, $this->form, $request));
        self::assertSame($organization, $customerUser->getOrganization());
    }

    public function testProcessAnotherUser(): void
    {
        $request = new Request();
        $request->initialize([], self::FORM_DATA);
        $request->setMethod('POST');

        $customerUser = $this->getCustomerUser(2);
        $organization = $this->getOrganization('test');

        $this->tokenAccessor->expects(self::once())
            ->method('getOrganization')
            ->willReturn($organization);
        $this->userManager->expects(self::never())
            ->method('sendWelcomeRegisteredByAdminEmail');
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $this->form->expects(self::once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->tokenAccessor->expects(self::once())
            ->method('getUserId')
            ->willReturn(1);
        $this->userManager->expects(self::never())
            ->method('reloadUser')
            ->with($customerUser);

        self::assertTrue($this->handler->process($customerUser, $this->form, $request));
        self::assertSame($organization, $customerUser->getOrganization());
    }
}
