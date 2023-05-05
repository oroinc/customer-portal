<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Tests\Unit\Processor\CustomizeFormData\CustomizeFormDataProcessorTestCase;
use Oro\Bundle\ApiBundle\Validator\Constraints\AccessGranted;
use Oro\Bundle\CustomerBundle\Api\Processor\SetCustomer;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class SetCustomerTest extends CustomizeFormDataProcessorTestCase
{
    private const CUSTOMER_FIELD_NAME = 'frontendOwner';

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
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
            self::CUSTOMER_FIELD_NAME
        );
    }

    private function getFormBuilder(): FormBuilderInterface
    {
        return $this->createFormBuilder()->create(
            '',
            FormType::class,
            ['data_class' => CustomerAddress::class]
        );
    }

    private function getForm(
        CustomerAddress $entity,
        array $customerFieldOptions = [],
        string $customerFieldName = self::CUSTOMER_FIELD_NAME
    ): FormInterface {
        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(
            $customerFieldName,
            FormType::class,
            array_merge(
                ['data_class' => Customer::class, 'constraints' => [new AccessGranted(['groups' => ['api']])]],
                $customerFieldOptions
            )
        );
        $form = $formBuilder->getForm();
        $form->setData($entity);

        return $form;
    }

    public function testProcessWhenFormHasSubmittedCustomerField()
    {
        $entity = new CustomerAddress();

        $form = $this->getForm($entity);
        $form->submit([self::CUSTOMER_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->tokenAccessor->expects(self::never())
            ->method('getUser');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertInstanceOf(Customer::class, $entity->getFrontendOwner());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get(self::CUSTOMER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasSubmittedCustomerFieldButItIsNotMapped()
    {
        $entity = new CustomerAddress();
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $form = $this->getForm($entity, ['mapped' => false]);
        $form->submit([self::CUSTOMER_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($customer, $entity->getFrontendOwner());

        self::assertEquals(
            [],
            $form->get(self::CUSTOMER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
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

        $form = $this->getForm($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($customer, $entity->getFrontendOwner());

        self::assertEquals(
            [],
            $form->get(self::CUSTOMER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedRenamedCustomerField()
    {
        $entity = new CustomerAddress();
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $form = $this->getForm($entity, ['property_path' => self::CUSTOMER_FIELD_NAME], 'renamedCustomer');

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($customer, $entity->getFrontendOwner());

        self::assertEquals(
            [],
            $form->get('renamedCustomer')->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedCustomerFieldAndNoCustomerUserInSecurityContext()
    {
        $entity = new CustomerAddress();

        $form = $this->getForm($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(null);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertNull($entity->getFrontendOwner());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get(self::CUSTOMER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedCustomerFieldAndSecurityContextContainsNotCustomerUser()
    {
        $entity = new CustomerAddress();

        $form = $this->getForm($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(new User());

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertNull($entity->getFrontendOwner());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get(self::CUSTOMER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedCustomerFieldButCustomerAlreadySetToEntity()
    {
        $entity = new CustomerAddress();
        $customer = new Customer();
        $entity->setFrontendOwner($customer);

        $form = $this->getForm($entity);

        $this->tokenAccessor->expects(self::never())
            ->method('getUser');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($customer, $entity->getFrontendOwner());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get(self::CUSTOMER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }
}
