<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Validator\Constraints\ScopeWithCustomerGroupAndCustomer;
use Oro\Bundle\CustomerBundle\Validator\Constraints\ScopeWithCustomerGroupAndCustomerValidator;
use Oro\Bundle\ScopeBundle\Tests\Unit\Stub\StubScope;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ScopeWithCustomerGroupAndCustomerValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new ScopeWithCustomerGroupAndCustomerValidator();
    }

    private function getCustomer(int $id): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);

        return $customer;
    }

    private function getCustomerGroup(int $id): CustomerGroup
    {
        $customerGroup = new CustomerGroup();
        ReflectionUtil::setId($customerGroup, $id);

        return $customerGroup;
    }

    public function testValidateEmptyCollection()
    {
        $value = $this->createMock(Collection::class);
        $value->expects($this->once())
            ->method('isEmpty')
            ->willReturn(true);

        $this->validator->validate($value, $this->createMock(Constraint::class));

        $this->assertNoViolation();
    }

    public function testValidateNotValidCollection()
    {
        $index = 1;
        $notValidScope = new StubScope([
            'customer' => $this->getCustomer(123),
            'customerGroup' => $this->getCustomerGroup(42)
        ]);

        $value = $this->createMock(Collection::class);
        $value->expects($this->once())
            ->method('isEmpty')
            ->willReturn(false);

        $value->expects($this->once())
            ->method('getValues')
            ->willReturn([$index => $notValidScope]);

        $constraint = new ScopeWithCustomerGroupAndCustomer();
        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->message)
            ->atPath('property.path[' . $index . ']')
            ->assertRaised();
    }
}
