<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Validator\Constraints\ScopeWithCustomerGroupAndCustomer;
use Oro\Bundle\CustomerBundle\Validator\Constraints\ScopeWithCustomerGroupAndCustomerValidator;
use Oro\Bundle\ScopeBundle\Tests\Unit\Stub\StubScope;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ScopeWithCustomerGroupAndCustomerValidatorTest extends ConstraintValidatorTestCase
{
    public function testValidateEmpty(): void
    {
        $constraint = new ScopeWithCustomerGroupAndCustomer();
        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateFail(): void
    {
        $scope = new StubScope();
        $scope->setCustomer($this->getCustomer(1));
        $scope->setCustomerGroup($this->getCustomerGroup(1));

        $constraint = new ScopeWithCustomerGroupAndCustomer();
        $this->validator->validate($scope, $constraint);
        $this->buildViolation($constraint->message)->assertRaised();
    }

    #[\Override]
    protected function createValidator(): ConstraintValidatorInterface
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
}
