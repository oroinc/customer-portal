<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Handler\CustomerAssignHelper;
use Oro\Bundle\CustomerBundle\Handler\CustomerDeleteHandlerExtension;
use Oro\Bundle\EntityBundle\Handler\EntityDeleteAccessDeniedExceptionFactory;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomerDeleteHandlerExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerAssignHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $customerAssignHelper;

    /** @var CustomerDeleteHandlerExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->customerAssignHelper = $this->createMock(CustomerAssignHelper::class);

        $this->extension = new CustomerDeleteHandlerExtension($this->customerAssignHelper);
        $this->extension->setDoctrine($this->createMock(ManagerRegistry::class));
        $this->extension->setAccessDeniedExceptionFactory(new EntityDeleteAccessDeniedExceptionFactory());
    }

    public function testAssertDeleteGrantedWithoutCustomerUsers()
    {
        $customer = new Customer();

        $this->customerAssignHelper->expects($this->once())
            ->method('hasAssignments')
            ->with($customer)
            ->willReturn(false);

        $this->extension->assertDeleteGranted($customer);
    }

    public function testAssertDeleteGrantedWithCustomerUsers()
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
