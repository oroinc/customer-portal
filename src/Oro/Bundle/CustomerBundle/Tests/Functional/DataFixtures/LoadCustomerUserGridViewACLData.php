<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\CustomerBundle\Entity\GridView;

class LoadCustomerUserGridViewACLData extends AbstractLoadACLData
{
    /**
     * {@inheritdoc}
     */
    protected function getAclResourceClassName()
    {
        return GridView::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedRoles()
    {
        return [
            self::ROLE_LOCAL,
            self::ROLE_DEEP
        ];
    }
}
