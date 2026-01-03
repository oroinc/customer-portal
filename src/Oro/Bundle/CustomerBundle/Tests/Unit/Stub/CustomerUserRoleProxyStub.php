<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Stub;

use Doctrine\Persistence\Proxy;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;

// phpcs:disable
class CustomerUserRoleProxyStub extends CustomerUserRole implements Proxy
{
    private $initialized = true;

    #[\Override]
    public function __load()
    {
        $this->initialized = true;
    }

    #[\Override]
    public function __isInitialized()
    {
        return $this->initialized;
    }
}
// phpcs:enable
