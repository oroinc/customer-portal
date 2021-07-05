<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerUserWithoutRole;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerUserWithoutRoleValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CustomerUserWithoutRoleValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new CustomerUserWithoutRoleValidator();
    }

    public function testDisabledCustomerWithoutRole()
    {
        $user = (new CustomerUser())->setEnabled(false);

        $constraint = new CustomerUserWithoutRole();
        $this->validator->validate(new ArrayCollection([$user]), $constraint);

        $this->assertNoViolation();
    }

    public function testEnabledUserWithoutRoles()
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
