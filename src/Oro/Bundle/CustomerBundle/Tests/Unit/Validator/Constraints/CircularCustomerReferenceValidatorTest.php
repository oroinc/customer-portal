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
    /** @var OwnerTreeInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $ownerTree;

    protected function setUp(): void
    {
        $this->ownerTree = $this->createMock(OwnerTreeInterface::class);
        parent::setUp();
    }

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

        $this->buildViolation($constraint->messageCircular)
            ->atPath('property.path.parent')
            ->setParameter('{{ parentName }}', 'test parent customer')
            ->setParameter('{{ customerName }}', 'test customer')
            ->assertRaised();
    }

    public function testValidateCircularChildCustomer()
    {
        $customer = new Customer();
        $customer->setId(1);
        $customer->setName('test customer');

        $parentCustomer = new Customer();
        $parentCustomer->setId(10);

        $customer->setParent($parentCustomer);

        $cyclicCustomer = new Customer();
        $cyclicCustomer->setId(5);
        $cyclicCustomer->setName('test cyclic customer');
        $customer->addChild($cyclicCustomer);

        $this->ownerTree->expects($this->exactly(2))
            ->method('getSubordinateBusinessUnitIds')
            ->willReturnMap([
                [1, [4, 6, 7]],
                [5, [4, 1, 7]]
            ]);

        $constraint = new CircularCustomerReference();
        $this->validator->validate($customer, $constraint);

        $this->buildViolation($constraint->messageCircularChild)
            ->atPath('property.path.children')
            ->setParameter('{{ childName }}', 'test cyclic customer')
            ->setParameter('{{ customerName }}', 'test customer')
            ->assertRaised();
    }

    public function testValidateCircularParentChildCustomer()
    {
        $customer = new Customer();
        $customer->setId(1);
        $customer->setName('test customer');

        $parentCustomer = new Customer();
        $parentCustomer->setId(5);
        $parentCustomer->setName('test parent customer');

        $customer->setParent($parentCustomer);
        $customer->addChild($parentCustomer);

        $this->ownerTree->expects($this->once())
            ->method('getSubordinateBusinessUnitIds')
            ->with(1)
            ->willReturn([4, 6, 7]);

        $constraint = new CircularCustomerReference();
        $this->validator->validate($customer, $constraint);

        $this->buildViolation($constraint->messageCircularChild)
            ->atPath('property.path.children')
            ->setParameter('{{ childName }}', 'test parent customer')
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
        $ownerTreeProvider = $this->createMock(FrontendOwnerTreeProvider::class);
        $ownerTreeProvider->expects($this->atLeastOnce())
            ->method('getTreeByBusinessUnit')
            ->willReturn($this->ownerTree);

        $this->ownerTree->expects($this->once())
            ->method('getSubordinateBusinessUnitIds')
            ->with(1)
            ->willReturn([4, 6, 7]);

        $validator = new CircularCustomerReferenceValidator($ownerTreeProvider);
        $validator->initialize($this->context);

        $customer = new Customer();
        $customer->setId(1);
        $parentCustomer = new Customer();
        $parentCustomer->setId(5);
        $customer->setParent($parentCustomer);

        $constraint = new CircularCustomerReference();
        $validator->validate($customer, $constraint);

        $this->assertNoViolation();
    }
}
