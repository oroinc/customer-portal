<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\CustomerAssignHelper;
use Oro\Bundle\CustomerBundle\Handler\CustomerDeleteHandler;
use Oro\Bundle\OrganizationBundle\Ownership\OwnerDeletionManager;

class CustomerDeleteHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerDeleteHandler */
    protected $handler;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $customerAssignHelper;

    protected function setUp()
    {
        $this->handler = new CustomerDeleteHandler();

        $ownerDeletionManager = $this->createMock(OwnerDeletionManager::class);
        $ownerDeletionManager->expects($this->any())
            ->method('isOwner')
            ->willReturn(false);
        $this->handler->setOwnerDeletionManager($ownerDeletionManager);

        $this->customerAssignHelper = $this->createMock(CustomerAssignHelper::class);
        $this->handler->setCustomerAssignHelper($this->customerAssignHelper);
    }

    public function testProcessDeleteOnCustomerWithoutCustomerUsers()
    {
        $customer = new Customer();

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())
            ->method('remove')
            ->with($customer);
        $em->expects($this->once())
            ->method('flush');

        $this->customerAssignHelper->expects($this->once())
            ->method('hasAssignments')
            ->with($customer)
            ->willReturn(false);

        $this->handler->processDelete($customer, $em);
    }

    /**
     * @expectedException \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     * @expectedExceptionMessage This customer has associated with other entities.
     */
    public function testProcessDeleteOnCustomerWithCustomerUsers()
    {
        $customer = new Customer();
        $customerUser = new CustomerUser();
        $customer->addUser($customerUser);

        $em = $this->createMock(EntityManager::class);

        $this->customerAssignHelper->expects($this->once())
            ->method('hasAssignments')
            ->with($customer)
            ->willReturn(true);

        $this->handler->processDelete($customer, $em);
    }
}
