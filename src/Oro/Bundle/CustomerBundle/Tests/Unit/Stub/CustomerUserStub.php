<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Stub;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * Extends CustomerUser with id property setter
 */
class CustomerUserStub extends CustomerUser
{
    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
