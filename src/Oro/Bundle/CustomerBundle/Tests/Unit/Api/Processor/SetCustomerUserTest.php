<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Tests\Unit\Processor\CustomizeFormData\CustomizeFormDataProcessorTestCase;
use Oro\Bundle\CustomerBundle\Api\Processor\SetCustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SetCustomerUserTest extends CustomizeFormDataProcessorTestCase
{
    private const CUSTOMER_USER_FIELD_NAME = 'frontendOwner';

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    private $tokenAccessor;

    /** @var SetCustomerUser */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->processor = new SetCustomerUser(
            PropertyAccess::createPropertyAccessor(),
            $this->tokenAccessor,
            self::CUSTOMER_USER_FIELD_NAME
        );
    }

    private function getFormBuilder(): FormBuilderInterface
    {
        return $this->createFormBuilder()->create(
            '',
            FormType::class,
            ['data_class' => CustomerUserAddress::class]
        );
    }

    public function testProcessWhenFormHasSubmittedCustomerUserField()
    {
        $entity = new CustomerUserAddress();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::CUSTOMER_USER_FIELD_NAME, FormType::class, ['data_class' => CustomerUser::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);
        $form->submit([self::CUSTOMER_USER_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->tokenAccessor->expects(self::never())
            ->method('getUser');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertInstanceOf(CustomerUser::class, $entity->getFrontendOwner());
    }

    public function testProcessWhenFormHasSubmittedCustomerUserFieldButItIsNotMapped()
    {
        $entity = new CustomerUserAddress();
        $user = new CustomerUser();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(
            self::CUSTOMER_USER_FIELD_NAME,
            FormType::class,
            ['data_class' => CustomerUser::class, 'mapped' => false]
        );
        $form = $formBuilder->getForm();
        $form->setData($entity);
        $form->submit([self::CUSTOMER_USER_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($user, $entity->getFrontendOwner());
    }

    public function testProcessWhenFormDoesNotHaveCustomerUserField()
    {
        $entity = new CustomerUserAddress();
        $user = new CustomerUser();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add('label');
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($user, $entity->getFrontendOwner());
    }

    public function testProcessWhenFormHasNotSubmittedCustomerUserField()
    {
        $entity = new CustomerUserAddress();
        $user = new CustomerUser();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::CUSTOMER_USER_FIELD_NAME, FormType::class, ['data_class' => CustomerUser::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($user, $entity->getFrontendOwner());
    }

    public function testProcessWhenFormHasNotSubmittedRenamedCustomerUserField()
    {
        $entity = new CustomerUserAddress();
        $user = new CustomerUser();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(
            'renamedCustomerUser',
            FormType::class,
            ['data_class' => CustomerUser::class, 'property_path' => self::CUSTOMER_USER_FIELD_NAME]
        );
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($user, $entity->getFrontendOwner());
    }

    public function testProcessWhenFormHasNotSubmittedCustomerUserFieldAndNoCustomerUserInSecurityContext()
    {
        $entity = new CustomerUserAddress();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::CUSTOMER_USER_FIELD_NAME, FormType::class, ['data_class' => CustomerUser::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(null);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertNull($entity->getFrontendOwner());
    }

    public function testProcessWhenFormHasNotSubmittedCustomerUserFieldAndSecurityContextContainsNotCustomerUser()
    {
        $entity = new CustomerUserAddress();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::CUSTOMER_USER_FIELD_NAME, FormType::class, ['data_class' => CustomerUser::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(new User());

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertNull($entity->getFrontendOwner());
    }

    public function testProcessWhenFormHasNotSubmittedCustomerUserFieldButCustomerUserAlreadySetToEntity()
    {
        $entity = new CustomerUserAddress();
        $user = new CustomerUser();
        $entity->setFrontendOwner($user);

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::CUSTOMER_USER_FIELD_NAME, FormType::class, ['data_class' => CustomerUser::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->tokenAccessor->expects(self::never())
            ->method('getUser');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($user, $entity->getFrontendOwner());
    }
}
