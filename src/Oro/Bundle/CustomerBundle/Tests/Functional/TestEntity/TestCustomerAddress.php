<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\TestEntity;

use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;

/**
 * CustomerAddress for testing purposes
 */
class TestCustomerAddress extends CustomerAddress
{
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }
}
