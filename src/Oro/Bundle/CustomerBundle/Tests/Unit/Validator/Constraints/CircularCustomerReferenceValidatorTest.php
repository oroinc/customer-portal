<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider;
use Oro\Bundle\CustomerBundle\Tests\Unit\Fixtures\Entity\Customer;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CircularCustomerReference;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CircularCustomerReferenceValidator;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CircularCustomerReferenceValidatorTest extends ConstraintValidatorTestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $ownerTree;

    protected function setUp()
    {
        $this->ownerTree = $this->createMock(OwnerTreeInterface::class);
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        $ownerTreeProvider = $this->createMock(OwnerTreeProviderInterface::class);
        $ownerTreeProvider->expects($this->any())
            ->method('getTree')
            ->willReturn($this->ownerTree);

        return new CircularCustomerReferenceValidator($ownerTreeProvider);
    }

    public function testValidateWithEmptyOwnerCustomer()
    {
        $customer = new Customer();
        $constraint = new CircularCustomerReference();

        $this->validator->validate($customer, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateValidOwnerCustomer()
    {
        $customer = new Customer();
        $customer->setId(1);
        $parentCustomer = new Customer();
        $parentCustomer->setId(5);
        $customer->setParent($parentCustomer);

        $this->ownerTree->expects($this->once())
            ->method('getSubordinateBusinessUnitIds')
            ->with(1)
            ->willReturn([4, 6, 7]);

        $constraint = new CircularCustomerReference();

        $this->validator->validate($customer, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateNotValidOwnerCustomer()
    {
        $customer = new Customer();
        $customer->setId(1);
        $customer->setName('test customer');
        $parentCustomer = new Customer();
        $parentCustomer->setId(5);
        $parentCustomer->setName('test parent customer');
        $customer->setParent($parentCustomer);

        $this->ownerTree->expects($this->once())
            ->method('getSubordinateBusinessUnitIds')
            ->with(1)
            ->willReturn([4, 5, 6, 7]);

        $constraint = new CircularCustomerReference();

        $this->validator->validate($customer, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ parentName }}', 'test parent customer')
            ->setParameter('{{ customerName }}', 'test customer')
            ->assertRaised();
    }

    public function testValidateNotValidOwnerCustomerPointingToItseld()
    {
        $customer = new Customer();
        $customer->setId(1);
        $customer->setName('test customer');
        $customer->setParent($customer);

        $this->ownerTree->expects($this->never())
            ->method('getSubordinateBusinessUnitIds');

        $constraint = new CircularCustomerReference();

        $this->validator->validate($customer, $constraint);

        $this->buildViolation($constraint->messageItself)
            ->setParameter('{{ customerName }}', 'test customer')
            ->assertRaised();
    }

    public function testValidateWithFrontendOwnerTreeProvider()
    {
        /** @var FrontendOwnerTreeProvider $ownerTreeProvider */
        $ownerTreeProvider = $this->createMock(FrontendOwnerTreeProvider::class);
        $ownerTreeProvider->expects($this->atLeastOnce())
            ->method('getTreeByBusinessUnit')
            ->willReturn($this->ownerTree);

        $this->ownerTree->expects($this->once())
            ->method('getSubordinateBusinessUnitIds')
            ->with(1)
            ->willReturn([4, 6, 7]);

        $this->validator = new CircularCustomerReferenceValidator($ownerTreeProvider);
        $this->validator->initialize($this->context);

        $customer = new Customer();
        $customer->setId(1);
        $parentCustomer = new Customer();
        $parentCustomer->setId(5);
        $customer->setParent($parentCustomer);
        $constraint = new CircularCustomerReference();

        $this->validator->validate($customer, $constraint);

        $this->assertNoViolation();
    }
}
