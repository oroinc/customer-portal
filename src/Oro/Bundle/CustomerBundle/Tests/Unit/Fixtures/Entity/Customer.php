<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Fixtures\Entity;

use Oro\Bundle\CustomerBundle\Entity\Customer as ParentCustomer;

class Customer extends ParentCustomer
{
    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
}
