<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerUserWithoutRole;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerUserWithoutRoleValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CustomerUserWithoutRoleValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new CustomerUserWithoutRoleValidator();
    }

    public function testUnexpectedConstraint()
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('test', $this->createMock(Constraint::class));
    }

    public function testValueIsNotCollection()
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('test', new CustomerUserWithoutRole());
    }

    public function testEmptyCustomerUserCollection()
    {
        $constraint = new CustomerUserWithoutRole();
        $this->validator->validate(new ArrayCollection(), $constraint);

        $this->assertNoViolation();
    }

    public function testCustomerUserWithRoles()
    {
        $user = new CustomerUser();
        $user->addUserRole(new CustomerUserRole());

        $constraint = new CustomerUserWithoutRole();
        $this->validator->validate(new ArrayCollection([$user]), $constraint);

        $this->assertNoViolation();
    }

    public function testDisabledCustomerUserWithoutRoles()
    {
        $user = (new CustomerUser())
            ->setEnabled(false);

        $constraint = new CustomerUserWithoutRole();
        $this->validator->validate(new ArrayCollection([$user]), $constraint);

        $this->assertNoViolation();
    }

    public function testEnabledCustomerUserWithoutRoles()
    {
        $user = (new CustomerUser())
            ->setFirstName('John')
            ->setLastName('Smith')
            ->setEnabled(true);

        $constraint = new CustomerUserWithoutRole();
        $this->validator->validate(new ArrayCollection([$user]), $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ userName }}', 'John Smith')
            ->assertRaised();
    }
}
