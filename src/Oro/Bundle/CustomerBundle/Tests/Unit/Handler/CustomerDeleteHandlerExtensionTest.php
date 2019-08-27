<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Handler\CustomerAssignHelper;
use Oro\Bundle\CustomerBundle\Handler\CustomerDeleteHandlerExtension;
use Oro\Bundle\EntityBundle\Handler\EntityDeleteAccessDeniedExceptionFactory;

class CustomerDeleteHandlerExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $customerAssignHelper;

    /** @var CustomerDeleteHandlerExtension */
    private $extension;

    protected function setUp()
    {
        $this->customerAssignHelper = $this->createMock(CustomerAssignHelper::class);

        $this->extension = new CustomerDeleteHandlerExtension(
            $this->customerAssignHelper
        );
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

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage The delete operation is forbidden. Reason: has associations to other entities.
     */
    public function testAssertDeleteGrantedWithCustomerUsers()
    {
        $customer = new Customer();

        $this->customerAssignHelper->expects($this->once())
            ->method('hasAssignments')
            ->with($customer)
            ->willReturn(true);

        $this->extension->assertDeleteGranted($customer);
    }
}
