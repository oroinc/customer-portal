<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Stub;

use Doctrine\Persistence\Proxy;

// @codingStandardsIgnoreStart
class CustomerUserRoleProxyStub extends CustomerUserRoleStub implements Proxy
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
