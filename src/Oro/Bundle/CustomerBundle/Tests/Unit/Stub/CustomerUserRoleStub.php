<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Stub;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;

class CustomerUserRoleStub extends CustomerUserRole
{
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
