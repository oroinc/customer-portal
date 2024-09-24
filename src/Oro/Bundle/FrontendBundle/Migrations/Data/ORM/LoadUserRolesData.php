<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\UserBundle\Entity\AbstractRole;
use Oro\Bundle\UserBundle\Entity\Role;

/**
 * Loads user roles.
 */
class LoadUserRolesData extends AbstractRolesData
{
    protected const ROLES_FILE_NAME = 'backend_roles.yml';

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadOrganizationAndBusinessUnitData::class];
    }

    #[\Override]
    protected function findEntity(ObjectManager $manager, string $name, ?string $label): ?AbstractRole
    {
        $entity = $manager->getRepository(Role::class)->findOneBy(['role' => $name]);
        if (null !== $entity && $label) {
            $entity->setLabel($label);
        }

        return $entity;
    }

    #[\Override]
    protected function createEntity(string $name, string $label): AbstractRole
    {
        $entity = new Role($name);
        $entity->setLabel($label);

        return $entity;
    }
}
