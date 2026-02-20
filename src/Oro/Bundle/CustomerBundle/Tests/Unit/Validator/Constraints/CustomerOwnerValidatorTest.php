<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerOwner;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerOwnerValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class CustomerOwnerValidatorTest extends ConstraintValidatorTestCase
{
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;

    private FrontendOwnershipMetadataProvider&MockObject $frontendOwnershipMetadataProvider;

    private PropertyAccessorInterface&MockObject $propertyAccessor;

    #[\Override]
    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->frontendOwnershipMetadataProvider = $this->createMock(FrontendOwnershipMetadataProvider::class);
        $this->propertyAccessor = $this->createMock(PropertyAccessorInterface::class);

        parent::setUp();
    }

    #[\Override]
    protected function createValidator(): CustomerOwnerValidator
    {
        return new CustomerOwnerValidator(
            $this->authorizationChecker,
            $this->frontendOwnershipMetadataProvider,
            $this->propertyAccessor
        );
    }

    public function testUnexpectedConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->validator->validate(
            $this->createMock(CustomerOwnerAwareInterface::class),
            $this->createMock(Constraint::class)
        );
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new CustomerOwner());

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->assertNoViolation();
    }

    public function testUnexpectedValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->validator->validate(new \stdClass(), $this->createMock(Constraint::class));
    }

    public function testCustomerUserBelongsToCustomer(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::once())
            ->method('getCustomer')
            ->willReturn($customer);

        $entity = $this->createMock(CustomerOwnerAwareInterface::class);
        $entity->expects(self::once())
            ->method('getCustomer')
            ->willReturn($customer);
        $entity->expects(self::once())
            ->method('getCustomerUser')
            ->willReturn($customerUser);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testCustomerUserDoesNotBelongToCustomer(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::once())
            ->method('getCustomer')
            ->willReturn($customer);

        $entity = $this->createMock(CustomerOwnerAwareInterface::class);
        $entity->expects(self::once())
            ->method('getCustomer')
            ->willReturn($this->createMock(Customer::class));
        $entity->expects(self::once())
            ->method('getCustomerUser')
            ->willReturn($customerUser);

        $this->authorizationChecker->expects(self::exactly(2))
            ->method('isGranted')
            ->willReturn(true);

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->buildViolation($constraint->message)
            ->assertRaised();
    }

    public function testEntityDoesNotHaveCustomer(): void
    {
        $entity = $this->createMock(CustomerOwnerAwareInterface::class);
        $entity->expects(self::once())
            ->method('getCustomer')
            ->willReturn(null);
        $entity->expects(self::never())
            ->method('getCustomerUser');

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testEntityDoesNotHaveCustomerUser(): void
    {
        $entity = $this->createMock(CustomerOwnerAwareInterface::class);
        $entity->expects(self::once())
            ->method('getCustomer')
            ->willReturn($this->createMock(Customer::class));
        $entity->expects(self::once())
            ->method('getCustomerUser')
            ->willReturn(null);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testCustomerUserDoesNotHaveCustomer(): void
    {
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::once())
            ->method('getCustomer')
            ->willReturn(null);

        $entity = $this->createMock(CustomerOwnerAwareInterface::class);
        $entity->expects(self::once())
            ->method('getCustomer')
            ->willReturn($this->createMock(Customer::class));
        $entity->expects(self::once())
            ->method('getCustomerUser')
            ->willReturn($customerUser);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testCustomerUserDoesNotBelongToCustomerAnNoPermissionToViewCustomer(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerUserCustomer = $this->createMock(Customer::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::once())
            ->method('getCustomer')
            ->willReturn($customerUserCustomer);

        $entity = $this->createMock(CustomerOwnerAwareInterface::class);
        $entity->expects(self::once())
            ->method('getCustomer')
            ->willReturn($customer);
        $entity->expects(self::once())
            ->method('getCustomerUser')
            ->willReturn($customerUser);

        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('VIEW', self::identicalTo($customer))
            ->willReturn(false);

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testCustomerUserDoesNotBelongToCustomerAnNoPermissionToViewCustomerUser(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerUserCustomer = $this->createMock(Customer::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::once())
            ->method('getCustomer')
            ->willReturn($customerUserCustomer);

        $entity = $this->createMock(CustomerOwnerAwareInterface::class);
        $entity->expects(self::once())
            ->method('getCustomer')
            ->willReturn($customer);
        $entity->expects(self::once())
            ->method('getCustomerUser')
            ->willReturn($customerUser);

        $this->authorizationChecker->expects(self::exactly(2))
            ->method('isGranted')
            ->willReturnMap([
                ['VIEW', $customer, true],
                ['VIEW', $customerUser, false],
            ]);

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testFrontendOwnershipMetadataEntityWithoutConfig(): void
    {
        $entity = new \stdClass();
        $entityClass = \stdClass::class;

        $this->frontendOwnershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($entityClass)
            ->willReturn(new FrontendOwnershipMetadata());
        $this->propertyAccessor->expects(self::never())
            ->method('getValue');
        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    /**
     * @dataProvider invalidFrontendOwnershipConfigProvider
     */
    public function testFrontendOwnershipMetadataEntityWithInvalidConfig(FrontendOwnershipMetadata $metadata): void
    {
        $entity = new \stdClass();
        $entityClass = \stdClass::class;

        $this->frontendOwnershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($entityClass)
            ->willReturn($metadata);
        $this->propertyAccessor->expects(self::never())
            ->method('getValue');
        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public static function invalidFrontendOwnershipConfigProvider(): array
    {
        return [
            'wrong owner type' => [
                new FrontendOwnershipMetadata(
                    'FRONTEND_CUSTOMER',
                    'owner',
                    'owner_id',
                    '',
                    '',
                    'customer',
                    'customer_id'
                ),
            ],
            'missing owner field name' => [new FrontendOwnershipMetadata()],
            'missing customer field name' => [
                new FrontendOwnershipMetadata(
                    'FRONTEND_USER',
                    'owner',
                    'owner_id',
                    '',
                    '',
                    '',
                    ''
                ),
            ],
        ];
    }

    public function testFrontendOwnershipMetadataCustomerUserBelongsToCustomer(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::once())
            ->method('getCustomer')
            ->willReturn($customer);

        $entity = new \stdClass();
        $entityClass = \stdClass::class;
        $metadata = $this->createFrontendOwnershipMetadata();

        $this->frontendOwnershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($entityClass)
            ->willReturn($metadata);
        $this->propertyAccessor->expects(self::exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [$entity, 'customer', $customer],
                [$entity, 'owner', $customerUser],
            ]);
        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testFrontendOwnershipMetadataCustomerUserDoesNotBelongToCustomer(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::once())
            ->method('getCustomer')
            ->willReturn($this->createMock(Customer::class));

        $entity = new \stdClass();
        $entityClass = \stdClass::class;
        $metadata = $this->createFrontendOwnershipMetadata();

        $this->frontendOwnershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($entityClass)
            ->willReturn($metadata);
        $this->propertyAccessor->expects(self::exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [$entity, 'customer', $customer],
                [$entity, 'owner', $customerUser],
            ]);
        $this->authorizationChecker->expects(self::exactly(2))
            ->method('isGranted')
            ->willReturn(true);

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->buildViolation($constraint->message)
            ->assertRaised();
    }

    public function testFrontendOwnershipMetadataEntityWithNullCustomer(): void
    {
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::never())
            ->method('getCustomer');

        $entity = new \stdClass();
        $entityClass = \stdClass::class;
        $metadata = $this->createFrontendOwnershipMetadata();

        $this->frontendOwnershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($entityClass)
            ->willReturn($metadata);
        $this->propertyAccessor->expects(self::exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [$entity, 'customer', null],
                [$entity, 'owner', $customerUser],
            ]);
        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testFrontendOwnershipMetadataEntityWithNullCustomerUser(): void
    {
        $customer = $this->createMock(Customer::class);

        $entity = new \stdClass();
        $entityClass = \stdClass::class;
        $metadata = $this->createFrontendOwnershipMetadata();

        $this->frontendOwnershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($entityClass)
            ->willReturn($metadata);
        $this->propertyAccessor->expects(self::exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [$entity, 'customer', $customer],
                [$entity, 'owner', null],
            ]);
        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testFrontendOwnershipMetadataCustomerUserHasNoCustomer(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::once())
            ->method('getCustomer')
            ->willReturn(null);

        $entity = new \stdClass();
        $entityClass = \stdClass::class;
        $metadata = $this->createFrontendOwnershipMetadata();

        $this->frontendOwnershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($entityClass)
            ->willReturn($metadata);
        $this->propertyAccessor->expects(self::exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [$entity, 'customer', $customer],
                [$entity, 'owner', $customerUser],
            ]);
        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testFrontendOwnershipMetadataCustomerUserDoesNotBelongNoPermissionToViewCustomer(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::once())
            ->method('getCustomer')
            ->willReturn($this->createMock(Customer::class));

        $entity = new \stdClass();
        $entityClass = \stdClass::class;
        $metadata = $this->createFrontendOwnershipMetadata();

        $this->frontendOwnershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($entityClass)
            ->willReturn($metadata);
        $this->propertyAccessor->expects(self::exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [$entity, 'customer', $customer],
                [$entity, 'owner', $customerUser],
            ]);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('VIEW', self::identicalTo($customer))
            ->willReturn(false);

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testFrontendOwnershipMetadataCustomerUserDoesNotBelongNoPermissionToViewCustomerUser(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects(self::once())
            ->method('getCustomer')
            ->willReturn($this->createMock(Customer::class));

        $entity = new \stdClass();
        $entityClass = \stdClass::class;
        $metadata = $this->createFrontendOwnershipMetadata();

        $this->frontendOwnershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($entityClass)
            ->willReturn($metadata);
        $this->propertyAccessor->expects(self::exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [$entity, 'customer', $customer],
                [$entity, 'owner', $customerUser],
            ]);
        $this->authorizationChecker->expects(self::exactly(2))
            ->method('isGranted')
            ->willReturnMap([
                ['VIEW', $customer, true],
                ['VIEW', $customerUser, false],
            ]);

        $constraint = new CustomerOwner();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    private function createFrontendOwnershipMetadata(): FrontendOwnershipMetadata
    {
        return new FrontendOwnershipMetadata(
            'FRONTEND_USER',
            'owner',
            'owner_id',
            '',
            '',
            'customer',
            'customer_id'
        );
    }
}
