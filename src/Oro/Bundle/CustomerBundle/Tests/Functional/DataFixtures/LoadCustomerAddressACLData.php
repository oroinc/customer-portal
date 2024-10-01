<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;

class LoadCustomerAddressACLData extends AbstractLoadACLData
{
    /**
     * @return string
     */
    #[\Override]
    protected function getAclResourceClassName()
    {
        return CustomerAddress::class;
    }

    /**
     * @return array
     */
    #[\Override]
    protected function getSupportedRoles()
    {
        return [
            self::ROLE_LOCAL,
            self::ROLE_LOCAL_VIEW_ONLY,
            self::ROLE_DEEP,
            self::ROLE_DEEP_VIEW_ONLY,
        ];
    }
}
