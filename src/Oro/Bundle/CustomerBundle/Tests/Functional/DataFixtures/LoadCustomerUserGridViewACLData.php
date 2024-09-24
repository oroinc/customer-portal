<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\CustomerBundle\Entity\GridView;

class LoadCustomerUserGridViewACLData extends AbstractLoadACLData
{
    #[\Override]
    protected function getAclResourceClassName()
    {
        return GridView::class;
    }

    #[\Override]
    protected function getSupportedRoles()
    {
        return [
            self::ROLE_LOCAL,
            self::ROLE_DEEP
        ];
    }
}
