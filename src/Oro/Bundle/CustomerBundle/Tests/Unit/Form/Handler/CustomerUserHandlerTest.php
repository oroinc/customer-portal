<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\FormHandlerTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerUserHandlerTest extends FormHandlerTestCase
{
    use EntityTrait;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Oro\Bundle\CustomerBundle\Entity\CustomerUserManager
     */
    protected $userManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FormInterface
     */
    protected $passwordGenerateForm;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FormInterface
     */
    protected $sendEmailForm;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface
     */
    protected $tokenAccessor;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TranslatorInterface
     */
    protected $translator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerUser
     */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = new CustomerUser();

        $this->userManager = $this->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\CustomerUserManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->passwordGenerateForm = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->sendEmailForm = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->translator = $this->createMock('Symfony\Contracts\Translation\TranslatorInterface');
        $this->logger = $this->createMock('Psr\Log\LoggerInterface');

        $this->handler = new CustomerUserHandler(
            $this->form,
            $this->request,
            $this->userManager,
            $this->tokenAccessor,
            $this->translator,
            $this->logger
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
        $organization = null;
        if ($isValid) {
            $organization = new Organization();
            $organization->setName('test');

            $this->tokenAccessor->expects($this->any())
                ->method('getOrganization')
                ->willReturn($organization);

            $this->userManager->expects($this->once())
                ->method('updateWebsiteSettings')
                ->with($this->entity);

            $this->form->expects($this->at(4))
                ->method('get')
                ->with('passwordGenerate')
                ->will($this->returnValue($this->passwordGenerateForm));

            $this->form->expects($this->at(5))
                ->method('get')
                ->with('sendEmail')
                ->will($this->returnValue($this->sendEmailForm));

            $this->passwordGenerateForm->expects($this->once())
                ->method('getData')
                ->will($this->returnValue(false));

            $this->sendEmailForm->expects($this->once())
                ->method('getData')
                ->will($this->returnValue(false));
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
            ->will($this->returnValue($isValid));

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

    /**
     * {@inheritdoc}
     */
    public function testProcessValidData()
    {
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->form->expects($this->at(4))
            ->method('get')
            ->with('passwordGenerate')
            ->will($this->returnValue($this->passwordGenerateForm));

        $this->form->expects($this->at(5))
            ->method('get')
            ->with('sendEmail')
            ->will($this->returnValue($this->sendEmailForm));

        $this->passwordGenerateForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(true));

        $this->sendEmailForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(true));

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->assertTrue($this->handler->process($this->entity));
    }

    public function testProcessCurrentUser()
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 1]);

        $organization = new Organization();
        $organization->setName('test');

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

    public function testProcessAnotherUser()
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 2]);

        $organization = new Organization();
        $organization->setName('test');

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

    protected function assertExistingUserSaveCalls(Organization $organization, CustomerUser $customerUser)
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
            ->will($this->returnValue(true));

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
    }
}
