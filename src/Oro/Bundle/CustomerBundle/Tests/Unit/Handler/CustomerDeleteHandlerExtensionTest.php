<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Handler\CustomerAssignHelper;
use Oro\Bundle\CustomerBundle\Handler\CustomerDeleteHandlerExtension;
use Oro\Bundle\EntityBundle\Handler\EntityDeleteAccessDeniedExceptionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomerDeleteHandlerExtensionTest extends TestCase
{
    private CustomerAssignHelper&MockObject $customerAssignHelper;
    private CustomerDeleteHandlerExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->customerAssignHelper = $this->createMock(CustomerAssignHelper::class);

        $this->extension = new CustomerDeleteHandlerExtension($this->customerAssignHelper);
        $this->extension->setDoctrine($this->createMock(ManagerRegistry::class));
        $this->extension->setAccessDeniedExceptionFactory(new EntityDeleteAccessDeniedExceptionFactory());
    }

    public function testAssertDeleteGrantedWithoutCustomerUsers(): void
    {
        $customer = new Customer();

        $this->customerAssignHelper->expects($this->once())
            ->method('hasAssignments')
            ->with($customer)
            ->willReturn(false);

        $this->extension->assertDeleteGranted($customer);
    }

    public function testAssertDeleteGrantedWithCustomerUsers(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: has associations to other entities.');

        $customer = new Customer();

        $this->customerAssignHelper->expects($this->once())
            ->method('hasAssignments')
            ->with($customer)
            ->willReturn(true);

        $this->extension->assertDeleteGranted($customer);
    }
}
