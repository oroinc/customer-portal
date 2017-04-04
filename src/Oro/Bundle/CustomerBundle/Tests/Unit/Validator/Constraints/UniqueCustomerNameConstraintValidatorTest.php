<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerNameConstraint;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerNameConstraintValidator;
use Oro\Component\Testing\Validator\AbstractConstraintValidatorTest;

class UniqueCustomerNameConstraintValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * @var CustomerRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    protected function setUp()
    {
        $this->repository = $this->createMock(CustomerRepository::class);

        parent::setUp();

        $this->constraint = new UniqueCustomerNameConstraint();
        $this->context->setConstraint($this->constraint);
    }

    /**
     * @dataProvider validDataProvider
     * @param mixed $value
     */
    public function testValidCustomerName($value)
    {
        $this->repository->method('countByName')
            ->willReturn(1);

        $this->validator->validate($value, $this->constraint);

        $this->assertNoViolation();
    }

    public function testInvalidCustomerName()
    {
        $customerName = 'TestCustomerGroup';
        $message = "oro.customer.message.ambiguous_customer_name";

        $this->repository->method('countByName')
            ->willReturn(2);

        $customer = new Customer();
        $customer->setName($customerName);
        $this->validator->validate($customer, $this->constraint);

        $this->buildViolation($message)
            ->setParameter('%name%', $customerName)
            ->assertRaised();
    }

    public function testDoesNotValidateNotSupportedValues()
    {
        $this->validator->validate(null, $this->constraint);

        $this->assertNoViolation();
    }

    public function validDataProvider()
    {
        $customer = new Customer();
        $customer->setName('TestCustomer');

        return [
            'string' => ['TestCustomerGroup'],
            'object' => [$customer],
            'integer' => [1]
        ];
    }

    /**
     * @return UniqueCustomerNameConstraintValidator
     */
    protected function createValidator()
    {
        return new UniqueCustomerNameConstraintValidator($this->repository);
    }
}
