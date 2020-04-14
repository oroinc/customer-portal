<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignUpdaterInterface;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerRelatedEntities;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerRelatedEntitiesValidator;
use Oro\Bundle\EntityBundle\Provider\EntityClassNameProviderInterface;
use Oro\Bundle\SaleBundle\Entity\Quote;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CustomerRelatedEntitiesValidatorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var CustomerUserReassignUpdaterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserReassignUpdater;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var EntityClassNameProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityClassNameProvider;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var UnitOfWork|\PHPUnit\Framework\MockObject\MockObject */
    private $uow;

    /** @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $context;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->authorizationChecker = self::createMock(AuthorizationCheckerInterface::class);
        $this->customerUserReassignUpdater = self::createMock(CustomerUserReassignUpdaterInterface::class);
        $this->registry = self::createMock(ManagerRegistry::class);
        $this->entityClassNameProvider = self::createMock(EntityClassNameProviderInterface::class);
        $this->em = self::createMock(EntityManager::class);
        $this->uow = self::createMock(UnitOfWork::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
    }

    public function testValidateNotCustomerUser()
    {
        $this->expectException(\Symfony\Component\Form\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "Oro\Bundle\CustomerBundle\Entity\CustomerUser", "stdClass" given'
        );

        $constraint = new CustomerRelatedEntities();

        $entity = new \stdClass();

        $this->registry->expects(self::never())
            ->method('getManagerForClass');

        $this->customerUserReassignUpdater->expects(self::never())
            ->method('getClassNamesToUpdate');

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->entityClassNameProvider->expects(self::never())
            ->method('getEntityClassName');

        $this->context->expects(self::never())
            ->method('buildViolation');

        $validator = new CustomerRelatedEntitiesValidator(
            $this->authorizationChecker,
            $this->customerUserReassignUpdater,
            $this->registry,
            $this->entityClassNameProvider
        );
        $validator->initialize($this->context);

        $validator->validate($entity, $constraint);
    }

    public function testValidateCreatingCustomerUser()
    {
        $constraint = new CustomerRelatedEntities();

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => null]);

        $this->registry->expects(self::never())
            ->method('getManagerForClass');

        $this->customerUserReassignUpdater->expects(self::never())
            ->method('getClassNamesToUpdate');

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->entityClassNameProvider->expects(self::never())
            ->method('getEntityClassName');

        $this->context->expects(self::never())
            ->method('buildViolation');

        $validator = new CustomerRelatedEntitiesValidator(
            $this->authorizationChecker,
            $this->customerUserReassignUpdater,
            $this->registry,
            $this->entityClassNameProvider
        );
        $validator->initialize($this->context);

        $validator->validate($customerUser, $constraint);
    }

    public function testValidateNoOriginalCustomer()
    {
        $constraint = new CustomerRelatedEntities();

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, [
            'id' => 35,
        ]);

        $originalCustomerUser = [
            'id' => 34,
        ];

        $this->registry->expects(self::once())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($this->em);

        $this->em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->uow->expects(self::once())
            ->method('getOriginalEntityData')
            ->willReturn($originalCustomerUser);

        $this->customerUserReassignUpdater->expects(self::never())
            ->method('getClassNamesToUpdate');

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->entityClassNameProvider->expects(self::never())
            ->method('getEntityClassName');

        $this->context->expects(self::never())
            ->method('buildViolation');

        $validator = new CustomerRelatedEntitiesValidator(
            $this->authorizationChecker,
            $this->customerUserReassignUpdater,
            $this->registry,
            $this->entityClassNameProvider
        );
        $validator->initialize($this->context);

        $validator->validate($customerUser, $constraint);
    }

    public function testValidateCustomerNotChanged()
    {
        $constraint = new CustomerRelatedEntities();

        /** @var Customer $originalCustomer */
        $originalCustomer = $this->getEntity(Customer::class, [
            'id' => 45,
        ]);
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, [
            'id' => 35,
            'customer' => $originalCustomer,
        ]);

        $originalCustomerUser = [
            'id' => 34,
            'customer' => $originalCustomer,
        ];

        $this->registry->expects(self::once())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($this->em);

        $this->em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->uow->expects(self::once())
            ->method('getOriginalEntityData')
            ->willReturn($originalCustomerUser);

        $this->customerUserReassignUpdater->expects(self::never())
            ->method('getClassNamesToUpdate');

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->entityClassNameProvider->expects(self::never())
            ->method('getEntityClassName');

        $this->context->expects(self::never())
            ->method('buildViolation');

        $validator = new CustomerRelatedEntitiesValidator(
            $this->authorizationChecker,
            $this->customerUserReassignUpdater,
            $this->registry,
            $this->entityClassNameProvider
        );
        $validator->initialize($this->context);

        $validator->validate($customerUser, $constraint);
    }

    public function testValidateCustomerChangedAuthorizationGranted()
    {
        $constraint = new CustomerRelatedEntities();

        /** @var Customer $customer */
        $customer = $this->getEntity(Customer::class, [
            'id' => 45,
        ]);
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, [
            'id' => 35,
            'customer' => $customer,
        ]);

        /** @var Customer $originalCustomer */
        $originalCustomer = $this->getEntity(Customer::class, [
            'id' => 44,
        ]);
        $originalCustomerUser = [
            'id' => 34,
            'customer' => $originalCustomer,
        ];

        $this->registry->expects(self::once())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($this->em);

        $this->em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->uow->expects(self::once())
            ->method('getOriginalEntityData')
            ->willReturn($originalCustomerUser);

        $entityClassesToUpdate = [
            'Oro\Bundle\OrderBundle\Entity\Order',
            'Oro\Bundle\ShoppingListBundle\Entity\ShoppingList',
        ];

        $this->customerUserReassignUpdater->expects(self::once())
            ->method('getClassNamesToUpdate')
            ->with($customerUser)
            ->willReturn($entityClassesToUpdate);

        $this->authorizationChecker->expects(self::exactly(2))
            ->method('isGranted')
            ->withConsecutive(
                ['EDIT;entity:Oro\Bundle\OrderBundle\Entity\Order'],
                ['EDIT;entity:Oro\Bundle\ShoppingListBundle\Entity\ShoppingList']
            )
            ->willReturnOnConsecutiveCalls(
                true,
                true
            );

        $this->entityClassNameProvider->expects(self::never())
            ->method('getEntityClassName');

        $this->context->expects(self::never())
            ->method('buildViolation');

        $validator = new CustomerRelatedEntitiesValidator(
            $this->authorizationChecker,
            $this->customerUserReassignUpdater,
            $this->registry,
            $this->entityClassNameProvider
        );
        $validator->initialize($this->context);

        $validator->validate($customerUser, $constraint);
    }

    public function testValidateCustomerChangedAuthorizationNotGranted()
    {
        $constraint = new CustomerRelatedEntities();

        /** @var Customer $customer */
        $customer = $this->getEntity(Customer::class, [
            'id' => 45,
        ]);
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, [
            'id' => 35,
            'customer' => $customer,
        ]);

        /** @var Customer $originalCustomer */
        $originalCustomer = $this->getEntity(Customer::class, [
            'id' => 44,
        ]);
        $originalCustomerUser = [
            'id' => 34,
            'customer' => $originalCustomer,
        ];

        $this->registry->expects(self::once())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($this->em);

        $this->em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->uow->expects(self::once())
            ->method('getOriginalEntityData')
            ->willReturn($originalCustomerUser);

        $entityClassesToUpdate = [
            'Oro\Bundle\OrderBundle\Entity\Order',
            'Oro\Bundle\ShoppingListBundle\Entity\ShoppingList',
            'Oro\Bundle\SaleBundle\Entity\Quote',
        ];

        $this->customerUserReassignUpdater->expects(self::once())
            ->method('getClassNamesToUpdate')
            ->with($customerUser)
            ->willReturn($entityClassesToUpdate);

        $this->authorizationChecker->expects(self::exactly(3))
            ->method('isGranted')
            ->withConsecutive(
                ['EDIT;entity:Oro\Bundle\OrderBundle\Entity\Order'],
                ['EDIT;entity:Oro\Bundle\ShoppingListBundle\Entity\ShoppingList'],
                ['EDIT;entity:Oro\Bundle\SaleBundle\Entity\Quote']
            )
            ->willReturnOnConsecutiveCalls(
                true,
                false,
                false
            );

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->entityClassNameProvider->expects(self::exactly(2))
            ->method('getEntityClassName')
            ->withConsecutive([ShoppingList::class], [Quote::class])
            ->willReturnOnConsecutiveCalls('Shopping List', 'Quote');

        $this->context->expects(self::once())
            ->method('buildViolation')
            ->with('oro.customer.message.no_permission_for_customer_related_entities')
            ->willReturn($builder);

        $builder->expects(self::once())
            ->method('setParameter')
            ->with('{{ entityNames }}', 'Shopping List, Quote')
            ->willReturnSelf();

        $builder->expects(self::once())
            ->method('atPath')
            ->with('customer')
            ->will($this->returnSelf());

        $builder->expects(self::once())
            ->method('addViolation');

        $validator = new CustomerRelatedEntitiesValidator(
            $this->authorizationChecker,
            $this->customerUserReassignUpdater,
            $this->registry,
            $this->entityClassNameProvider
        );
        $validator->initialize($this->context);

        $validator->validate($customerUser, $constraint);
    }
}
