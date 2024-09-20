<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;

class CustomerUserStub extends CustomerUser
{
    private ?EnumOptionInterface $authStatus = null;

    public function __construct(?int $id = null)
    {
        parent::__construct();

        if ($id !== null) {
            $this->id = $id;
        }
    }

    public function getAuthStatus(): ?EnumOptionInterface
    {
        return $this->authStatus;
    }

    public function setAuthStatus(EnumOptionInterface $enum = null)
    {
        $this->authStatus = $enum;

        return $this;
    }
}
