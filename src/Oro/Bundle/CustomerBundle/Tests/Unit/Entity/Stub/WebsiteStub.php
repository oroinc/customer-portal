<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class WebsiteStub extends Website
{
    /** @var CustomerUserRole|null */
    protected $defaultRole;

    /** @var CustomerUserRole|null */
    protected $guestRole;

    /**
     * @return null|CustomerUserRole
     */
    public function getDefaultRole()
    {
        return $this->defaultRole;
    }

    public function setDefaultRole(CustomerUserRole $defaultRole)
    {
        $this->defaultRole = $defaultRole;
    }

    /**
     * @return null|CustomerUserRole
     */
    public function getGuestRole()
    {
        return $this->guestRole;
    }

    public function setGuestRole(CustomerUserRole $guestRole)
    {
        $this->guestRole = $guestRole;
    }
}
