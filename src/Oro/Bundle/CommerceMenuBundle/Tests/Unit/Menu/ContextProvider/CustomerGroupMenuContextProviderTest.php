<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\ContextProvider;

use Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider\CustomerGroupMenuContextProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;

use Oro\Component\Testing\Unit\EntityTrait;

class CustomerGroupMenuContextProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    public function testGetContexts()
    {
        $customer = $this->getEntity(CustomerGroup::class, ['id' => 5]);
        $provider = new CustomerGroupMenuContextProvider;

        $this->assertEquals([['customerGroup' => 5]], $provider->getContexts($customer));
    }
}
