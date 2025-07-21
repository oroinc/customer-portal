<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Ownership;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\Ownership\FrontendCustomerAwareTrait;
use PHPUnit\Framework\TestCase;

class FrontendCustomerAwareTraitTest extends TestCase
{
    /** @var FrontendCustomerAwareTrait */
    private $frontendCustomerAwareTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendCustomerAwareTrait = $this->getMockForTrait(FrontendCustomerAwareTrait::class);
    }

    public function testSetCustomer(): void
    {
        $customer = $this->createMock(Customer::class);
        $this->frontendCustomerAwareTrait->setCustomer($customer);

        $this->assertSame($customer, $this->frontendCustomerAwareTrait->getCustomer());
    }
}
