<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\ORM;

use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;

/**
 * Loads ACL data to grant profile and configuration update capabilities to frontend roles.
 *
 * This migration fixture loads role permissions from a YAML configuration file to ensure that frontend users
 * have the ACL permissions for updating their profiles and managing configuration settings.
 */
class AddProfileAndConfigUpdateCapabilitiesToRoles extends AbstractLoadAclData
{
    #[\Override]
    public function getDataPath()
    {
        return '@OroFrontendBundle/Migrations/Data/ORM/data/backend_roles.yml';
    }
}
