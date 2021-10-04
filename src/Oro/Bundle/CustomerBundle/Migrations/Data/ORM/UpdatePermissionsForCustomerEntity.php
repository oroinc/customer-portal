<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\ObjectIdentityHelper;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;

/**
 * Updates permissions for Customer entity for all storefront roles.
 */
class UpdatePermissionsForCustomerEntity extends AbstractUpdatePermissions
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function load(ObjectManager $manager)
    {
        if (!$this->container->get(ApplicationState::class)->isInstalled()) {
            return;
        }

        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $roles = $manager->getRepository(CustomerUserRole::class)->findAll();
        $oidDescriptor = ObjectIdentityHelper::encodeIdentityString(EntityAclExtension::NAME, Customer::class);
        $oid = $aclManager->getOid($oidDescriptor);
        foreach ($roles as $role) {
            $sid = $aclManager->getSid($role);
            $accessLevelName = $role->getRole() === 'ROLE_FRONTEND_ADMINISTRATOR'
                ? 'DEEP'
                : 'LOCAL';
            $privilegePermissions = $this->getPrivilegePermissions(
                $this->getPrivileges($sid, 'commerce'),
                $oidDescriptor
            );
            $maskBuilders = $aclManager->getAllMaskBuilders($oid);
            foreach ($maskBuilders as $maskBuilder) {
                foreach ($privilegePermissions as $privilegePermission) {
                    if ($privilegePermission->getAccessLevel() !== AccessLevel::NONE_LEVEL) {
                        $permission = $privilegePermission->getName() . '_' . $accessLevelName;
                        if ($maskBuilder->hasMaskForPermission($permission)) {
                            $maskBuilder->add($permission);
                        }
                    }
                }
                $aclManager->setPermission($sid, $oid, $maskBuilder->get());
            }
        }
        $aclManager->flush();
    }
}
