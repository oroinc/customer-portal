<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\WorkflowBundle\Migrations\Data\ORM\UpdateAccessLevelsOfTransitionsPermissions as BaseMigration;

/**
 * Finds workflow transitions permissions with invalid access level and updates them to the maximum allowed access
 * level for customer user roles.
 */
class UpdateAccessLevelsOfTransitionsPermissions extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [LoadCustomerUserRoles::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoles(ObjectManager $manager): array
    {
        return $manager->getRepository(CustomerUserRole::class)->findAll();
    }
}
