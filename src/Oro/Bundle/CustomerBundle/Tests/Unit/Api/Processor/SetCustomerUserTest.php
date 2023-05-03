<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Tests\Unit\Processor\CustomizeFormData\CustomizeFormDataProcessorTestCase;
use Oro\Bundle\ApiBundle\Validator\Constraints\AccessGranted;
use Oro\Bundle\CustomerBundle\Api\Processor\SetCustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class SetCustomerUserTest extends CustomizeFormDataProcessorTestCase
{
    private const CUSTOMER_USER_FIELD_NAME = 'frontendOwner';

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
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

    private function getForm(
        CustomerUserAddress $entity,
        array $customerUserFieldOptions = [],
        string $customerUserFieldName = self::CUSTOMER_USER_FIELD_NAME
    ): FormInterface {
        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(
            $customerUserFieldName,
            FormType::class,
            array_merge(
                ['data_class' => CustomerUser::class, 'constraints' => [new AccessGranted(['groups' => ['api']])]],
                $customerUserFieldOptions
            )
        );
        $form = $formBuilder->getForm();
        $form->setData($entity);

        return $form;
    }

    public function testProcessWhenFormHasSubmittedCustomerUserField()
    {
        $entity = new CustomerUserAddress();

        $form = $this->getForm($entity);
        $form->submit([self::CUSTOMER_USER_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->tokenAccessor->expects(self::never())
            ->method('getUser');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertInstanceOf(CustomerUser::class, $entity->getFrontendOwner());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get(self::CUSTOMER_USER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasSubmittedCustomerUserFieldButItIsNotMapped()
    {
        $entity = new CustomerUserAddress();
        $user = new CustomerUser();

        $form = $this->getForm($entity, ['mapped' => false]);
        $form->submit([self::CUSTOMER_USER_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($user, $entity->getFrontendOwner());

        self::assertEquals(
            [],
            $form->get(self::CUSTOMER_USER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
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

        $form = $this->getForm($entity);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($user, $entity->getFrontendOwner());

        self::assertEquals(
            [],
            $form->get(self::CUSTOMER_USER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedRenamedCustomerUserField()
    {
        $entity = new CustomerUserAddress();
        $user = new CustomerUser();

        $form = $this->getForm($entity, ['property_path' => self::CUSTOMER_USER_FIELD_NAME], 'renamedCustomerUser');

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($user, $entity->getFrontendOwner());

        self::assertEquals(
            [],
            $form->get('renamedCustomerUser')->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedCustomerUserFieldAndNoCustomerUserInSecurityContext()
    {
        $entity = new CustomerUserAddress();

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
            $form->get(self::CUSTOMER_USER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedCustomerUserFieldAndSecurityContextContainsNotCustomerUser()
    {
        $entity = new CustomerUserAddress();

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
            $form->get(self::CUSTOMER_USER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedCustomerUserFieldButCustomerUserAlreadySetToEntity()
    {
        $entity = new CustomerUserAddress();
        $user = new CustomerUser();
        $entity->setFrontendOwner($user);

        $form = $this->getForm($entity);

        $this->tokenAccessor->expects(self::never())
            ->method('getUser');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($user, $entity->getFrontendOwner());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get(self::CUSTOMER_USER_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }
}
