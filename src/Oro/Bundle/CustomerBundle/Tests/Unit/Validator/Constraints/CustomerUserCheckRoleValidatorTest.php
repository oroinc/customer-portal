<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerUserCheckRole;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerUserCheckRoleValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CustomerUserCheckRoleValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new CustomerUserCheckRoleValidator();
    }

    public function testCustomerUserWithRole()
    {
        $role = $this->createMock(CustomerUserRole::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects($this->once())
            ->method('getRoles')
            ->willReturn([$role]);
        $customerUser->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $constraint = new CustomerUserCheckRole();
        $this->validator->validate($customerUser, $constraint);

        $this->assertNoViolation();
    }

    public function testEnabledCustomerWithoutRole()
    {
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);
        $customerUser->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $constraint = new CustomerUserCheckRole();
        $this->validator->validate($customerUser, $constraint);

        $this->buildViolation($constraint->message)
            ->assertRaised();
    }

    public function testDisabledCustomerWithoutRole()
    {
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects($this->never())
            ->method('getRoles')
            ->willReturn(null);
        $customerUser->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $constraint = new CustomerUserCheckRole();
        $this->validator->validate($customerUser, $constraint);

        $this->assertNoViolation();
    }
}
