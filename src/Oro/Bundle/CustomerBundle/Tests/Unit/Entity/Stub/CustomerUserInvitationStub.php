<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;

class CustomerUserInvitationStub extends CustomerUserInvitation
{
    private ?EnumOptionInterface $status = null;

    public function getStatus(): ?EnumOptionInterface
    {
        return $this->status;
    }

    public function setStatus(EnumOptionInterface $status): static
    {
        $this->status = $status;

        return $this;
    }
}
