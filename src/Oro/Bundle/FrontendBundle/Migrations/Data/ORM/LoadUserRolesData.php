<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\ORM;

use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\UserBundle\Entity\AbstractRole;
use Oro\Bundle\UserBundle\Entity\Role;

/**
 * Loads user roles.
 */
class LoadUserRolesData extends AbstractRolesData
{
    protected const ROLES_FILE_NAME = 'backend_roles.yml';

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadOrganizationAndBusinessUnitData::class];
    }

    /**
     * {@inheritDoc}
     */
    protected function createEntity(string $name, string $label): AbstractRole
    {
        $entity = new Role($name);
        $entity->setLabel($label);

        return $entity;
    }
}
