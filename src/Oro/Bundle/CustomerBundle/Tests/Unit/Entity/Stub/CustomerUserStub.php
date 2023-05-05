<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

class CustomerUserStub extends CustomerUser
{
    private ?AbstractEnumValue $authStatus = null;

    public function __construct(?int $id = null)
    {
        parent::__construct();

        if ($id !== null) {
            $this->id = $id;
        }
    }

    public function getAuthStatus(): ?AbstractEnumValue
    {
        return $this->authStatus;
    }

    public function setAuthStatus(AbstractEnumValue $enum = null)
    {
        $this->authStatus = $enum;

        return $this;
    }
}
