<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\ContextProvider;

use Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider\CustomerMenuContextProvider;
use Oro\Bundle\CustomerBundle\Entity\Customer;

use Oro\Component\Testing\Unit\EntityTrait;

class CustomerMenuContextProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    public function testGetContexts()
    {
        $customer = $this->getEntity(Customer::class, ['id' => 5]);
        $provider = new CustomerMenuContextProvider;

        $this->assertEquals([['customer' => 5]], $provider->getContexts($customer));
    }
}
