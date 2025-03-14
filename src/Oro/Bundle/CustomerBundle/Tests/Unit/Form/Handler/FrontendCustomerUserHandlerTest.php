<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\CustomerUserStub;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmail;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use Oro\Bundle\FormBundle\Event\FormHandler\Events;
use Oro\Bundle\FormBundle\Event\FormHandler\FormProcessEvent;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\RequestWebsiteProvider;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;

class FrontendCustomerUserHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserManager|MockObject */
    private $userManager;

    /** @var DoctrineHelper|MockObject */
    private $doctrineHelper;

    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcher;

    /** @var RequestWebsiteProvider|MockObject */
    private $requestWebsiteProvider;

    /** @var Request|MockObject */
    private $request;

    /** @var FormInterface|MockObject */
    private $form;

    /**
     * @var ConfigManager|MockObject
     */
    private $configManager;

    /** @var FrontendCustomerUserHandler */
    private $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->userManager = $this->createMock(CustomerUserManager::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->requestWebsiteProvider = $this->createMock(RequestWebsiteProvider::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->request = new Request();

        $this->form = $this->createMock(FormInterface::class);
        $this->handler = new FrontendCustomerUserHandler(
            $this->eventDispatcher,
            $this->doctrineHelper,
            $this->requestWebsiteProvider,
            $this->userManager,
            $this->configManager
        );
        $this->handler->setIgnoreNotUniqueEmailValidationError(false);
    }

    public function testProcessInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data should be instance of %s, but %s is given');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Data should be instance of %s, but %s is given',
                CustomerUser::class,
                \stdClass::class
            )
        );

        $this->handler->process(new \stdClass(), $this->form, $this->request);
    }

    public function testProcessFlushException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test flush exception');

        $entity = new CustomerUserStub();
        $entity->setEmail('test@test.com');

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('beginTransaction');
        $em->expects(self::once())
            ->method('rollback');

        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($entity)
            ->willThrowException(new \Exception('Test flush exception'));

        $this->handler->process($entity, $this->form, $this->request);
    }

    public function testProcessUnexpectedRequestMethod(): void
    {
        $entity = new CustomerUserStub();

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::never())
            ->method('submit');
        $this->form->expects(self::never())
            ->method('isValid');

        $this->request->setMethod('GET');

        self::assertFalse($this->handler->process($entity, $this->form, $this->request));
    }

    public function testProcessNewCustomerUser(): void
    {
        $entity = new CustomerUserStub();
        $entity->setEmail('test@test.com');

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('beginTransaction');
        $em->expects(self::once())
            ->method('commit');

        $website = new Website();
        $this->requestWebsiteProvider->expects(self::once())
            ->method('getWebsite')
            ->willReturn($website);

        $this->userManager->expects(self::once())
            ->method('register')
            ->with($entity);
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($entity);

        $this->assertProcessAfterEventsTriggered($this->form, $entity);

        self::assertTrue($this->handler->process($entity, $this->form, $this->request));
        self::assertSame($website, $entity->getWebsite());
    }

    public function testProcessNewCustomerUserAndNoCurrentWebsite(): void
    {
        $entity = new CustomerUserStub();
        $entity->setEmail('test@test.com');

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('beginTransaction');
        $em->expects(self::once())
            ->method('commit');

        $this->requestWebsiteProvider->expects(self::once())
            ->method('getWebsite')
            ->willReturn(null);

        $this->userManager->expects(self::once())
            ->method('register')
            ->with($entity);
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($entity);

        $this->assertProcessAfterEventsTriggered($this->form, $entity);

        self::assertTrue($this->handler->process($entity, $this->form, $this->request));
        self::assertNull($entity->getWebsite());
    }

    public function testProcessExistingCustomerUser(): void
    {
        $entity = new CustomerUserStub();
        ReflectionUtil::setId($entity, 1);

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('beginTransaction');
        $em->expects(self::once())
            ->method('commit');

        $this->userManager->expects(self::never())
            ->method('register');
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($entity);
        $this->userManager->expects(self::once())
            ->method('reloadUser')
            ->with($entity);

        $this->assertProcessAfterEventsTriggered($this->form, $entity);

        $this->configManager->expects($this->never())
            ->method('get');
        $this->userManager->expects($this->never())
            ->method('sendDuplicateEmailNotification');

        self::assertTrue($this->handler->process($entity, $this->form, $this->request));
    }

    public function testProcessExistingCustomerUserEnumerationProtectionEnabled(): void
    {
        $entity = new CustomerUserStub();
        $entity->setEmail('test@test.com');

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $emailFieldError = $this->createMock(FormError::class);
        $emailField = $this->createMock(Form::class);
        $emailFieldError->expects($this->once())
            ->method('getOrigin')
            ->willReturn($emailField);
        $emailField->expects($this->once())
            ->method('clearErrors');
        $cause = new ConstraintViolation(
            message: 'non_unique_email',
            messageTemplate: null,
            parameters: [],
            root: null,
            propertyPath: null,
            invalidValue: 'test@test.com',
            code: UniqueCustomerUserNameAndEmail::NOT_UNIQUE_EMAIL
        );
        $emailFieldError->expects($this->any())
            ->method('getCause')
            ->willReturn($cause);

        $formErrorIterator = new FormErrorIterator($this->form, [$emailFieldError]);
        $this->form->expects($this->once())
            ->method('getErrors')
            ->willReturn($formErrorIterator);
        $emailField->expects($this->once())
            ->method('getErrors')
            ->willReturn($formErrorIterator);

        $this->request->setMethod('POST');

        $this->doctrineHelper->expects(self::never())
            ->method('getEntityManager');

        $this->userManager->expects(self::never())
            ->method('register');
        $this->userManager->expects(self::never())
            ->method('updateUser')
            ->with($entity);
        $this->userManager->expects(self::never())
            ->method('reloadUser')
            ->with($entity);

        $this->eventDispatcher->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [new FormProcessEvent($this->form, $entity), Events::BEFORE_FORM_DATA_SET],
                [new FormProcessEvent($this->form, $entity), Events::BEFORE_FORM_SUBMIT]
            );

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.email_enumeration_protection_enabled')
            ->willReturn(true);
        $this->userManager->expects($this->once())
            ->method('findUserByEmail')
            ->with($entity->getEmail())
            ->willReturn($entity);
        $this->userManager->expects($this->once())
            ->method('sendDuplicateEmailNotification')
            ->with($entity);

        $this->handler->setIgnoreNotUniqueEmailValidationError(true);

        self::assertTrue($this->handler->process($entity, $this->form, $this->request));
    }

    public function testProcessExistingCustomerUserEnumerationProtectionEnabledInvalidForm(): void
    {
        $entity = new CustomerUserStub();
        $entity->setEmail('test@test.com');

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $emailFieldError = $this->createMock(FormError::class);
        $emailField = $this->createMock(Form::class);
        $emailFieldError->expects($this->any())
            ->method('getOrigin')
            ->willReturn($emailField);
        $emailField->expects($this->never())
            ->method('clearErrors');
        $cause = new ConstraintViolation(
            message: 'invalid_email',
            messageTemplate: null,
            parameters: [],
            root: null,
            propertyPath: null,
            invalidValue: 'test_test.com',
            code: 'some_code'
        );
        $emailFieldError->expects($this->any())
            ->method('getCause')
            ->willReturn($cause);

        $formErrorIterator = new FormErrorIterator($this->form, [$emailFieldError]);
        $this->form->expects($this->once())
            ->method('getErrors')
            ->willReturn($formErrorIterator);
        $emailField->expects($this->never())
            ->method('getErrors');

        $this->request->setMethod('POST');

        $this->doctrineHelper->expects(self::never())
            ->method('getEntityManager');

        $this->userManager->expects(self::never())
            ->method('register');
        $this->userManager->expects(self::never())
            ->method('updateUser')
            ->with($entity);
        $this->userManager->expects(self::never())
            ->method('reloadUser')
            ->with($entity);

        $this->eventDispatcher->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [new FormProcessEvent($this->form, $entity), Events::BEFORE_FORM_DATA_SET],
                [new FormProcessEvent($this->form, $entity), Events::BEFORE_FORM_SUBMIT]
            );

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.email_enumeration_protection_enabled')
            ->willReturn(true);
        $this->userManager->expects($this->never())
            ->method('findUserByEmail');
        $this->userManager->expects($this->never())
            ->method('sendDuplicateEmailNotification');

        $this->handler->setIgnoreNotUniqueEmailValidationError(true);

        self::assertFalse($this->handler->process($entity, $this->form, $this->request));
    }

    public function testProcessExistingCustomerUserEnumerationProtectionEnabledInvalidFormTwoErrors(): void
    {
        $entity = new CustomerUserStub();
        $entity->setEmail('test@test.com');

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $emailField = $this->createMock(Form::class);
        $emailField->expects($this->once())
            ->method('clearErrors');

        $emailFieldError1 = $this->createMock(FormError::class);
        $emailFieldError1->expects($this->any())
            ->method('getOrigin')
            ->willReturn($emailField);
        $emailFieldError2 = $this->createMock(FormError::class);
        $emailFieldError2->expects($this->any())
            ->method('getOrigin')
            ->willReturn($emailField);

        $cause = new ConstraintViolation(
            message: 'invalid_email',
            messageTemplate: null,
            parameters: [],
            root: null,
            propertyPath: null,
            invalidValue: 'test@test.com',
            code: 'some_code'
        );
        $cause2 = new ConstraintViolation(
            message: 'non_unique_email',
            messageTemplate: null,
            parameters: [],
            root: null,
            propertyPath: null,
            invalidValue: 'test@test.com',
            code: UniqueCustomerUserNameAndEmail::NOT_UNIQUE_EMAIL
        );
        $emailFieldError1->expects($this->any())
            ->method('getCause')
            ->willReturn($cause);
        $emailFieldError2->expects($this->any())
            ->method('getCause')
            ->willReturn($cause2);

        $emailField->expects($this->once())
            ->method('addError')
            ->with($emailFieldError1);

        $formErrorIterator = new FormErrorIterator($this->form, [$emailFieldError1, $emailFieldError2]);
        $this->form->expects($this->once())
            ->method('getErrors')
            ->willReturn($formErrorIterator);
        $emailField->expects($this->once())
            ->method('getErrors')
            ->willReturn($formErrorIterator);

        $this->request->setMethod('POST');

        $this->doctrineHelper->expects(self::never())
            ->method('getEntityManager');

        $this->userManager->expects(self::never())
            ->method('register');
        $this->userManager->expects(self::never())
            ->method('updateUser')
            ->with($entity);
        $this->userManager->expects(self::never())
            ->method('reloadUser')
            ->with($entity);

        $this->eventDispatcher->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [new FormProcessEvent($this->form, $entity), Events::BEFORE_FORM_DATA_SET],
                [new FormProcessEvent($this->form, $entity), Events::BEFORE_FORM_SUBMIT]
            );

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.email_enumeration_protection_enabled')
            ->willReturn(true);
        $this->userManager->expects($this->never())
            ->method('findUserByEmail');
        $this->userManager->expects($this->never())
            ->method('sendDuplicateEmailNotification');

        $this->handler->setIgnoreNotUniqueEmailValidationError(true);

        self::assertFalse($this->handler->process($entity, $this->form, $this->request));
    }

    private function assertProcessAfterEventsTriggered(FormInterface $form, CustomerUser $entity): void
    {
        $this->eventDispatcher->expects(self::exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [new FormProcessEvent($form, $entity), Events::BEFORE_FORM_DATA_SET],
                [new FormProcessEvent($form, $entity), Events::BEFORE_FORM_SUBMIT],
                [new AfterFormProcessEvent($form, $entity), Events::BEFORE_FLUSH],
                [new AfterFormProcessEvent($form, $entity), Events::AFTER_FLUSH]
            );
    }
}
