<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmail;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmailValidator;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueCustomerUserNameAndEmailValidatorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var CustomerUserManager|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserManager;

    /** @var UniqueCustomerUserNameAndEmail|\PHPUnit\Framework\MockObject\MockObject */
    private $constraint;

    /** @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $executionContext;

    /** @var ConstraintViolationBuilderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $constraintViolationBuilder;

    /** @var UniqueCustomerUserNameAndEmailValidator */
    private $validator;

    protected function setUp(): void
    {
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->constraint = new UniqueCustomerUserNameAndEmail();

        $this->validator = new UniqueCustomerUserNameAndEmailValidator($this->customerUserManager);
        $this->validator->initialize($this->executionContext);
    }

    public function testValidateNewCustomerUserEmailIsUnique()
    {
        $email = 'foo';
        /** @var CustomerUser $newCustomerUser */
        $newCustomerUser = $this->getEntity(CustomerUser::class, ['email' => $email]);

        $this->customerUserManager
            ->expects(self::once())
            ->method('findUserByEmail')
            ->with($email)
            ->willReturn(null);

        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($newCustomerUser, $this->constraint);
    }

    public function testValidateCustomerUserEmailIsUnique()
    {
        $email = 'foo';
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, ['email' => $email, 'id' => 1]);

        $this->customerUserManager
            ->expects(self::once())
            ->method('findUserByEmail')
            ->with($email)
            ->willReturn($customerUser);

        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($customerUser, $this->constraint);
    }

    public function testValidateGuestCustomerUserEmailIsNotUnique()
    {
        $email = 'foo';
        /** @var CustomerUser $guestCustomerUser */
        $guestCustomerUser = $this->getEntity(CustomerUser::class, ['email' => $email, 'isGuest' => true]);
        /** @var CustomerUser $existingCustomerUser */
        $existingCustomerUser = $this->getEntity(CustomerUser::class, ['email' => $email]);

        $this->customerUserManager
            ->expects(self::never())
            ->method('findUserByEmail')
            ->with($email)
            ->willReturn($existingCustomerUser);

        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($guestCustomerUser, $this->constraint);
    }

    public function testValidateCustomerUserEmailIsNotUnique()
    {
        $newUserEmail = 'foo';
        /** @var CustomerUser $newCustomerUser */
        $newCustomerUser = $this->getEntity(CustomerUser::class, ['email' => $newUserEmail, 'id' => 1]);
        /** @var CustomerUser $existingCustomerUser */
        $existingCustomerUser = $this->getEntity(CustomerUser::class, ['email' => $newUserEmail, 'id' => 2]);

        $this->customerUserManager
            ->expects(self::once())
            ->method('findUserByEmail')
            ->with($newUserEmail)
            ->willReturn($existingCustomerUser);

        $this->executionContext
            ->expects(self::once())
            ->method('buildViolation')
            ->with($this->constraint->message)
            ->willReturn($this->constraintViolationBuilder);

        $this->constraintViolationBuilder
            ->expects(self::at(0))
            ->method('atPath')
            ->with('email')
            ->willReturnSelf();
        $this->constraintViolationBuilder
            ->expects(self::at(1))
            ->method('setInvalidValue')
            ->with($newUserEmail)
            ->willReturnSelf();
        $this->constraintViolationBuilder
            ->expects(self::at(2))
            ->method('addViolation')
            ->willReturnSelf();

        $this->validator->validate($newCustomerUser, $this->constraint);
    }

    public function testValidateCustomerUserEmailIsNull()
    {
        /** @var CustomerUser $newCustomerUser */
        $newCustomerUser = $this->getEntity(CustomerUser::class, ['email' => null, 'id' => 1]);

        $this->customerUserManager->expects(self::never())
            ->method('findUserByEmail');

        $this->constraintViolationBuilder->expects(self::never())
            ->method('addViolation');

        $this->validator->validate($newCustomerUser, $this->constraint);
    }
}
