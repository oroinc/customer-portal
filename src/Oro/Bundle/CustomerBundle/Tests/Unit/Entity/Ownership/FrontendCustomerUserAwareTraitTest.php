<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Ownership;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Ownership\FrontendCustomerUserAwareTrait;

class FrontendCustomerUserAwareTraitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FrontendCustomerUserAwareTrait | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $frontendCustomerUserAwareTrait;

    protected function setUp()
    {
        $this->frontendCustomerUserAwareTrait = $this->getMockForTrait(FrontendCustomerUserAwareTrait::class);
    }

    public function testSetCustomerUser()
    {
        /** @var CustomerUser|\PHPUnit\Framework\MockObject\MockObject $customerUser **/
        $customerUser = $this->createMock(CustomerUser::class);
        $this->frontendCustomerUserAwareTrait->setCustomerUser($customerUser);

        $this->assertSame($customerUser, $this->frontendCustomerUserAwareTrait->getCustomerUser());
    }

    public function testSetCustomer()
    {
        /** @var Customer|\PHPUnit\Framework\MockObject\MockObject $customer **/
        $customer = $this->createMock(Customer::class);
        $this->frontendCustomerUserAwareTrait->setCustomer($customer);

        $this->assertSame($customer, $this->frontendCustomerUserAwareTrait->getCustomer());
    }
}
