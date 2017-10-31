<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\ORM;

use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;

class AddProfileAndConfigUpdateCapabilitiesToRoles extends AbstractLoadAclData
{
    /**
     * {@inheritdoc}
     */
    public function getDataPath()
    {
        return '@OroFrontendBundle/Migrations/Data/ORM/data/backend_roles.yml';
    }
}
