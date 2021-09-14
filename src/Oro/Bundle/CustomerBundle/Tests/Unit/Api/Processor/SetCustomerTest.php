<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Tests\Unit\Processor\CustomizeFormData\CustomizeFormDataProcessorTestCase;
use Oro\Bundle\CustomerBundle\Api\Processor\SetCustomer;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SetCustomerTest extends CustomizeFormDataProcessorTestCase
{
    private const CUSTOMER_USER_FIELD_NAME = 'frontendOwner';

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    private $tokenAccessor;

    /** @var SetCustomer */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->processor = new SetCustomer(
            PropertyAccess::createPropertyAccessor(),
            $this->tokenAccessor,
            self::CUSTOMER_USER_FIELD_NAME
        );
    }

    /**
     * @return FormBuilderInterface
     */
    private function getFormBuilder()
    {
        return $this->createFormBuilder()->create(
            '',
            FormType::class,
            ['data_class' => CustomerAddress::class]
        );
    }

    public function testProcessWhenFormHasSubmittedCustomerField()
    {
        $entity = new CustomerAddress();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::CUSTOMER_USER_FIELD_NAME, FormType::class, ['data_class' => Customer::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);
        $form->submit([self::CUSTOMER_USER_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->tokenAccessor->expects(self::never())
            ->method('getUser');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertInstanceOf(Customer::class, $entity->getFrontendOwner());
    }

    public function testProcessWhenFormHasSubmittedCustomerFieldButItIsNotMapped()
    {
        $entity = new CustomerAddress();
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(
            self::CUSTOMER_USER_FIELD_NAME,
            FormType::class,
            ['data_class' => Customer::class, 'mapped' => false]
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

        self::assertSame($customer, $entity->getFrontendOwner());
    }

    public function testProcessWhenFormDoesNotHaveCustomerField()
    {
        $entity = new CustomerAddress();
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

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

        self::assertSame($customer, $entity->getFrontendOwner());
    }

    public function testProcessWhenFormHasNotSubmittedCustomerField()
    {
        $entity = new CustomerAddress();
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::CUSTOMER_USER_FIELD_NAME, FormType::class, ['data_class' => Customer::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($customer, $entity->getFrontendOwner());
    }

    public function testProcessWhenFormHasNotSubmittedRenamedCustomerField()
    {
        $entity = new CustomerAddress();
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(
            'renamedCustomer',
            FormType::class,
            ['data_class' => Customer::class, 'property_path' => self::CUSTOMER_USER_FIELD_NAME]
        );
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($customer, $entity->getFrontendOwner());
    }

    public function testProcessWhenFormHasNotSubmittedCustomerFieldAndNoCustomerUserInSecurityContext()
    {
        $entity = new CustomerAddress();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::CUSTOMER_USER_FIELD_NAME, FormType::class, ['data_class' => Customer::class]);
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

    public function testProcessWhenFormHasNotSubmittedCustomerFieldAndSecurityContextContainsNotCustomerUser()
    {
        $entity = new CustomerAddress();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::CUSTOMER_USER_FIELD_NAME, FormType::class, ['data_class' => Customer::class]);
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

    public function testProcessWhenFormHasNotSubmittedCustomerFieldButCustomerAlreadySetToEntity()
    {
        $entity = new CustomerAddress();
        $customer = new Customer();
        $entity->setFrontendOwner($customer);

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::CUSTOMER_USER_FIELD_NAME, FormType::class, ['data_class' => Customer::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->tokenAccessor->expects(self::never())
            ->method('getUser');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($customer, $entity->getFrontendOwner());
    }
}
