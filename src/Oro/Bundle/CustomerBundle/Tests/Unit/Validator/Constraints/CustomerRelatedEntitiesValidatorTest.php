<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignUpdaterInterface;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerRelatedEntities;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerRelatedEntitiesValidator;
use Oro\Bundle\EntityBundle\Provider\EntityClassNameProviderInterface;
use Oro\Bundle\SaleBundle\Entity\Quote;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CustomerRelatedEntitiesValidatorTest extends ConstraintValidatorTestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var CustomerUserReassignUpdaterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserReassignUpdater;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var EntityClassNameProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityClassNameProvider;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var UnitOfWork|\PHPUnit\Framework\MockObject\MockObject */
    private $uow;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->customerUserReassignUpdater = $this->createMock(CustomerUserReassignUpdaterInterface::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->entityClassNameProvider = $this->createMock(EntityClassNameProviderInterface::class);
        $this->em = $this->createMock(EntityManager::class);
        $this->uow = $this->createMock(UnitOfWork::class);
        parent::setUp();
    }

    protected function createValidator(): CustomerRelatedEntitiesValidator
    {
        return new CustomerRelatedEntitiesValidator(
            $this->authorizationChecker,
            $this->customerUserReassignUpdater,
            $this->doctrine,
            $this->entityClassNameProvider
        );
    }

    private function getCustomer(int $id): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);

        return $customer;
    }

    private function getCustomerUser(int $id = null, Customer $customer = null): CustomerUser
    {
        $customerUser = new CustomerUser();
        if (null !== $id) {
            ReflectionUtil::setId($customerUser, $id);
        }
        if (null !== $customer) {
            $customerUser->setCustomer($customer);
        }

        return $customerUser;
    }

    public function testGetTargets()
    {
        $constraint = new CustomerRelatedEntities();
        self::assertEquals(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }

    public function testValidateNotCustomerUser()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "Oro\Bundle\CustomerBundle\Entity\CustomerUser", "stdClass" given'
        );

        $this->doctrine->expects(self::never())
            ->method('getManagerForClass');

        $this->customerUserReassignUpdater->expects(self::never())
            ->method('getClassNamesToUpdate');

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->entityClassNameProvider->expects(self::never())
            ->method('getEntityClassName');

        $constraint = new CustomerRelatedEntities();
        $this->validator->validate(new \stdClass(), $constraint);
    }

    public function testValidateCreatingCustomerUser()
    {
        $customerUser = $this->getCustomerUser();

        $this->doctrine->expects(self::never())
            ->method('getManagerForClass');

        $this->customerUserReassignUpdater->expects(self::never())
            ->method('getClassNamesToUpdate');

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->entityClassNameProvider->expects(self::never())
            ->method('getEntityClassName');

        $constraint = new CustomerRelatedEntities();
        $this->validator->validate($customerUser, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateNoOriginalCustomer()
    {
        $customerUser = $this->getCustomerUser(35);

        $originalCustomerUser = [
            'id' => 34,
        ];

        $this->doctrine->expects(self::once())
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

        $constraint = new CustomerRelatedEntities();
        $this->validator->validate($customerUser, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateCustomerNotChanged()
    {
        $originalCustomer = $this->getCustomer(45);
        $customerUser = $this->getCustomerUser(35, $originalCustomer);

        $originalCustomerUser = [
            'id' => 34,
            'customer' => $originalCustomer,
        ];

        $this->doctrine->expects(self::once())
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

        $constraint = new CustomerRelatedEntities();
        $this->validator->validate($customerUser, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateCustomerChangedAuthorizationGranted()
    {
        $customer = $this->getCustomer(45);
        $customerUser = $this->getCustomerUser(35, $customer);

        $originalCustomer = $this->getCustomer(44);
        $originalCustomerUser = [
            'id' => 34,
            'customer' => $originalCustomer,
        ];

        $this->doctrine->expects(self::once())
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

        $constraint = new CustomerRelatedEntities();
        $this->validator->validate($customerUser, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateCustomerChangedAuthorizationNotGranted()
    {
        $customer = $this->getCustomer(45);
        $customerUser = $this->getCustomerUser(35, $customer);

        $originalCustomer = $this->getCustomer(44);
        $originalCustomerUser = [
            'id' => 34,
            'customer' => $originalCustomer,
        ];

        $this->doctrine->expects(self::once())
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

        $this->entityClassNameProvider->expects(self::exactly(2))
            ->method('getEntityClassName')
            ->withConsecutive([ShoppingList::class], [Quote::class])
            ->willReturnOnConsecutiveCalls('Shopping List', 'Quote');

        $constraint = new CustomerRelatedEntities();
        $this->validator->validate($customerUser, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ entityNames }}', 'Shopping List, Quote')
            ->atPath('property.path.customer')
            ->assertRaised();
    }
}
