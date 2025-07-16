<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Ownership;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Ownership\FrontendCustomerUserAwareTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontendCustomerUserAwareTraitTest extends TestCase
{
    /** @var FrontendCustomerUserAwareTrait&MockObject */
    private $frontendCustomerUserAwareTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendCustomerUserAwareTrait = $this->getMockForTrait(FrontendCustomerUserAwareTrait::class);
    }

    public function testSetCustomerUser(): void
    {
        $customerUser = $this->createMock(CustomerUser::class);
        $this->frontendCustomerUserAwareTrait->setCustomerUser($customerUser);

        $this->assertSame($customerUser, $this->frontendCustomerUserAwareTrait->getCustomerUser());
    }

    public function testSetCustomer(): void
    {
        $customer = $this->createMock(Customer::class);
        $this->frontendCustomerUserAwareTrait->setCustomer($customer);

        $this->assertSame($customer, $this->frontendCustomerUserAwareTrait->getCustomer());
    }
}
