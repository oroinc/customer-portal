<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;

/**
 * The base class for data fixtures that do mass updating of permissions for storefront roles.
 */
abstract class AbstractMassUpdateCustomerUserRolePermissions extends AbstractUpdatePermissions implements
    DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadCustomerUserRoles::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $this->updateRoles($aclManager, $manager);
        $aclManager->flush();
    }

    protected function updateRoles(AclManager $aclManager, ObjectManager $manager)
    {
        foreach ($this->getACLData() as $roleName => $aclData) {
            $role = $this->getRole($manager, $roleName, CustomerUserRole::class);
            foreach ($aclData as $oidDescriptor => $permissions) {
                $this->replacePermissions(
                    $aclManager,
                    $role,
                    $aclManager->getOid($oidDescriptor),
                    $permissions
                );
            }
        }
    }

    /**
     * Return array of ACL data
     *
     * @return array [role name => [oid descriptor => [permission, ...], ...], ...]
     */
    abstract protected function getACLData(): array;
}
