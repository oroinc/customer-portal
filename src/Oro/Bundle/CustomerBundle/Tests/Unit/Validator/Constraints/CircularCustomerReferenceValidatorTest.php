<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CircularCustomerReference;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CircularCustomerReferenceValidator;
use Oro\Component\Testing\Validator\AbstractConstraintValidatorTest;

class CircularCustomerReferenceValidatorTest extends AbstractConstraintValidatorTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->constraint = new CircularCustomerReference();
        $this->context->setConstraint($this->constraint);
    }

    public function testCircularReferenceValidationPassed()
    {
        $customer1 = new Customer();
        $this->setCustomerId($customer1, 1);

        $customer2 = new Customer();
        $this->setCustomerId($customer2, 2);
        $customer2->setParent($customer1);

        $customer3 = new Customer();
        $this->setCustomerId($customer3, 3);
        $customer3->setParent($customer2);

        $this->object = $customer3;
        $this->context->setNode($this->value, $this->object, $this->metadata, $this->propertyPath);

        $this->validator->validate($customer2, $this->constraint);

        $this->assertNoViolation();
    }

    public function testCircularReferenceValidationFailed()
    {
        $message = 'oro.customer.message.circular_customer_reference';

        $customerName1 = 'Customer 1';
        $customer1 = new Customer();
        $this->setCustomerId($customer1, 1);
        $customer1->setName($customerName1);

        $this->object = $customer1;
        $this->context->setNode($this->value, $this->object, $this->metadata, $this->propertyPath);

        $customer2 = new Customer();
        $this->setCustomerId($customer2, 2);
        $customer2->setParent($customer1);

        $customerName3 = 'Customer 3';
        $customer3 = new Customer();
        $this->setCustomerId($customer3, 3);
        $customer3->setName($customerName3);
        $customer3->setParent($customer2);

        $customer1->setParent($customer3);

        $this->validator->validate($customer3, $this->constraint);

        $this->buildViolation($message)
            ->setParameter('{{ parentName }}', $customer3->getName())
            ->setParameter('{{ customerName }}', $customer1->getName())
            ->assertRaised();
    }

    /**
     * @param Customer $customer
     * @param $id
     */
    protected function setCustomerId(Customer $customer, $id)
    {
        $reflection = new \ReflectionProperty(Customer::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($customer, $id);
    }

    /**
     * @return CircularCustomerReferenceValidator
     */
    protected function createValidator()
    {
        return new CircularCustomerReferenceValidator();
    }
}
