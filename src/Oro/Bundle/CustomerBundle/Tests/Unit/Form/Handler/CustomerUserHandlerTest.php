<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\FormHandlerTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerUserHandlerTest extends FormHandlerTestCase
{
    /** @var CustomerUserManager|\PHPUnit\Framework\MockObject\MockObject */
    private $userManager;

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $passwordGenerateForm;

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $sendEmailForm;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var CustomerUser */
    protected $entity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = new CustomerUser();
        $this->userManager = $this->createMock(CustomerUserManager::class);
        $this->passwordGenerateForm = $this->createMock(FormInterface::class);
        $this->sendEmailForm = $this->createMock(FormInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new CustomerUserHandler(
            $this->form,
            $this->request,
            $this->userManager,
            $this->tokenAccessor,
            $this->translator,
            $this->logger
        );
    }

    public function testProcessUnsupportedRequest(): void
    {
        $this->request->setMethod('GET');

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     */
    public function testProcessSupportedRequest(string $method, bool $isValid, bool $isProcessed): void
    {
        $organization = null;
        if ($isValid) {
            $organization = $this->getOrganization('test');

            $this->tokenAccessor->expects($this->any())
                ->method('getOrganization')
                ->willReturn($organization);

            $this->userManager->expects($this->once())
                ->method('updateWebsiteSettings')
                ->with($this->entity);

            $this->form->expects($this->exactly(2))
                ->method('get')
                ->willReturnMap([
                    ['passwordGenerate', $this->passwordGenerateForm],
                    ['sendEmail', $this->sendEmailForm]
                ]);

            $this->passwordGenerateForm->expects($this->once())
                ->method('getData')
                ->willReturn(false);

            $this->sendEmailForm->expects($this->once())
                ->method('getData')
                ->willReturn(false);
            $this->userManager->expects($this->once())
                ->method('updateUser')
                ->with($this->entity);
        } else {
            $this->userManager->expects($this->never())
                ->method('updateUser')
                ->with($this->entity);
        }

        $this->form->expects($this->any())
            ->method('isValid')
            ->willReturn($isValid);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->assertEquals($isProcessed, $this->handler->process($this->entity));
        if ($organization) {
            $this->assertEquals($organization, $this->entity->getOrganization());
        }
    }

    public function testProcessValidData(): void
    {
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->form->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['passwordGenerate', $this->passwordGenerateForm],
                ['sendEmail', $this->sendEmailForm]
            ]);

        $this->passwordGenerateForm->expects($this->once())
            ->method('getData')
            ->willReturn(true);

        $this->sendEmailForm->expects($this->once())
            ->method('getData')
            ->willReturn(true);

        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->assertTrue($this->handler->process($this->entity));
    }

    public function testProcessCurrentUser(): void
    {
        $customerUser = $this->getCustomerUser(1);
        $organization = $this->getOrganization('test');

        $this->assertExistingUserSaveCalls($organization, $customerUser);

        $this->tokenAccessor->expects($this->once())
            ->method('getUserId')
            ->willReturn(1);
        $this->userManager->expects($this->once())
            ->method('reloadUser')
            ->with($customerUser);

        $this->assertEquals(true, $this->handler->process($customerUser));
        if ($organization) {
            $this->assertEquals($organization, $customerUser->getOrganization());
        }
    }

    public function testProcessAnotherUser(): void
    {
        $customerUser = $this->getCustomerUser(2);
        $organization = $this->getOrganization('test');

        $this->assertExistingUserSaveCalls($organization, $customerUser);

        $this->tokenAccessor->expects($this->once())
            ->method('getUserId')
            ->willReturn(1);
        $this->userManager->expects($this->never())
            ->method('reloadUser')
            ->with($customerUser);

        $this->assertEquals(true, $this->handler->process($customerUser));
        if ($organization) {
            $this->assertEquals($organization, $customerUser->getOrganization());
        }
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

    private function assertExistingUserSaveCalls(Organization $organization, CustomerUser $customerUser): void
    {
        $this->tokenAccessor->expects($this->any())
            ->method('getOrganization')
            ->willReturn($organization);
        $this->userManager->expects($this->never())
            ->method('sendWelcomeRegisteredByAdminEmail');
        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($customerUser);
        $this->form->expects($this->any())
            ->method('isValid')
            ->willReturn(true);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
    }
}
