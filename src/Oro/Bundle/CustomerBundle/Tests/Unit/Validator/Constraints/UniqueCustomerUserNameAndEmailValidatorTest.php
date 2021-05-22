<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmail;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmailValidator;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueCustomerUserNameAndEmailValidatorTest extends ConstraintValidatorTestCase
{
    /** @var CustomerUserManager|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserManager;

    protected function setUp(): void
    {
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        parent::setUp();
    }

    protected function createValidator()
    {
        return new UniqueCustomerUserNameAndEmailValidator($this->customerUserManager);
    }

    private function getCustomerUser(?string $email, int $id = null): CustomerUser
    {
        $customerUser = new CustomerUser();
        $customerUser->setEmail($email);
        if (null !== $id) {
            ReflectionUtil::setId($customerUser, $id);
        }

        return $customerUser;
    }

    public function testValidateNewCustomerUserEmailIsUnique()
    {
        $email = 'foo';
        $newCustomerUser = $this->getCustomerUser($email);

        $this->customerUserManager->expects(self::once())
            ->method('findUserByEmail')
            ->with($email)
            ->willReturn(null);

        $constraint = new UniqueCustomerUserNameAndEmail();
        $this->validator->validate($newCustomerUser, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateCustomerUserEmailIsUnique()
    {
        $email = 'foo';
        $customerUser = $this->getCustomerUser($email, 1);

        $this->customerUserManager->expects(self::once())
            ->method('findUserByEmail')
            ->with($email)
            ->willReturn($customerUser);

        $constraint = new UniqueCustomerUserNameAndEmail();
        $this->validator->validate($customerUser, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateGuestCustomerUserEmailIsNotUnique()
    {
        $email = 'foo';
        $guestCustomerUser = $this->getCustomerUser($email);
        $guestCustomerUser->setIsGuest(true);
        $existingCustomerUser = $this->getCustomerUser($email);

        $this->customerUserManager->expects(self::never())
            ->method('findUserByEmail')
            ->with($email)
            ->willReturn($existingCustomerUser);

        $constraint = new UniqueCustomerUserNameAndEmail();
        $this->validator->validate($guestCustomerUser, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateCustomerUserEmailIsNotUnique()
    {
        $newUserEmail = 'foo';
        $newCustomerUser = $this->getCustomerUser($newUserEmail, 1);
        $existingCustomerUser = $this->getCustomerUser($newUserEmail, 2);

        $this->customerUserManager->expects(self::once())
            ->method('findUserByEmail')
            ->with($newUserEmail)
            ->willReturn($existingCustomerUser);

        $constraint = new UniqueCustomerUserNameAndEmail();
        $this->validator->validate($newCustomerUser, $constraint);

        $this->buildViolation($constraint->message)
            ->atPath('property.path.email')
            ->setInvalidValue($newUserEmail)
            ->assertRaised();
    }

    public function testValidateCustomerUserEmailIsNull()
    {
        $newCustomerUser = $this->getCustomerUser(null, 1);

        $this->customerUserManager->expects(self::never())
            ->method('findUserByEmail');

        $constraint = new UniqueCustomerUserNameAndEmail();
        $this->validator->validate($newCustomerUser, $constraint);

        $this->assertNoViolation();
    }
}
