<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Stub;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * Extends CustomerUser with id property setter
 */
class CustomerUserStub extends CustomerUser
{
    public function __construct(?int $id = null)
    {
        parent::__construct();

        $this->id = $id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
