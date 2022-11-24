<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Stub;

use Doctrine\Persistence\Proxy;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;

// @codingStandardsIgnoreStart
class CustomerUserRoleProxyStub extends CustomerUserRole implements Proxy
{
    private $initialized = true;

    public function __load()
    {
        $this->initialized = true;
    }

    public function __isInitialized()
    {
        return $this->initialized;
    }
}
// @codingStandardsIgnoreEnd
