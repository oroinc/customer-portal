<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmail;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmailValidator;
use Oro\Component\Testing\Unit\EntityTrait;

class UniqueCustomerUserNameAndEmailValidatorTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $customerUserRepository;

    /** @var UniqueCustomerUserNameAndEmailValidator */
    private $validator;

    protected function setUp()
    {
        $this->customerUserRepository = $this->createMock(EntityRepository::class);
        $this->validator = new UniqueCustomerUserNameAndEmailValidator($this->customerUserRepository);
    }

    public function testValidationSucceedsWhenUsersWithSuchEmailOrUsernameDoesntExist()
    {
        $newCustomer = $this->getEntity(
            CustomerUser::class,
            [
                'username' => 'foo',
                'email' => 'foo',
            ]
        );

        $this->customerUserRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'foo', 'isGuest' => false])
            ->willReturn(null);

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())->method('buildViolation');

        /** @var UniqueCustomerUserNameAndEmail|\PHPUnit_Framework_MockObject_MockObject $constraint */
        $constraint = $this->createMock(UniqueCustomerUserNameAndEmail::class);

        $this->validator->initialize($context);
        $this->validator->validate($newCustomer, $constraint);
    }

    public function testValidationSucceedsWhenOnlyGuestUsersExistWithSuchUsernameOrEmail()
    {
        $newCustomer = $this->getEntity(
            CustomerUser::class,
            [
                'username' => 'foo',
                'email' => 'foo',
            ]
        );

        $this->customerUserRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'foo', 'isGuest' => false])
            ->willReturn(null);

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())->method('buildViolation');

        /** @var UniqueCustomerUserNameAndEmail|\PHPUnit_Framework_MockObject_MockObject $constraint */
        $constraint = $this->createMock(UniqueCustomerUserNameAndEmail::class);

        $this->validator->initialize($context);
        $this->validator->validate($newCustomer, $constraint);
    }

    public function testValidationFailsWhenNonGuestUserWithSuchEmailOrUsernameExists()
    {
        $existingCustomer = $this->getEntity(
            CustomerUser::class,
            [
                'username' => 'foo',
                'email' => 'foo',
                'guest' => false,
            ]
        );
        $newCustomer = $this->getEntity(
            CustomerUser::class,
            [
                'username' => 'foo',
                'email' => 'foo',
            ]
        );

        $this->customerUserRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'foo', 'isGuest' => false])
            ->willReturn($existingCustomer);

        /** @var ConstraintViolationBuilderInterface|\PHPUnit_Framework_MockObject_MockObject $violationBuilder */
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->expects($this->at(0))->method('atPath')->willReturn($violationBuilder);
        $violationBuilder->expects($this->at(1))->method('setInvalidValue')->willReturn($violationBuilder);
        $violationBuilder->expects($this->at(2))->method('addViolation')->willReturn($violationBuilder);

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->once())->method('buildViolation')->willReturn($violationBuilder);

        /** @var UniqueCustomerUserNameAndEmail|\PHPUnit_Framework_MockObject_MockObject $constraint */
        $constraint = $this->createMock(UniqueCustomerUserNameAndEmail::class);

        $this->validator->initialize($context);
        $this->validator->validate($newCustomer, $constraint);
    }
}
