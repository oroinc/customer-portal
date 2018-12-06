<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\ObjectIdentityHelper;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * Updates the access level for Customer entity for frontend roles.
 */
class UpdatePermissionsForCustomerEntity extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $isInstalled = $this->container->hasParameter('installed') && $this->container->getParameter('installed');
        if (!$isInstalled) {
            return;
        }

        $privilegeRepository = $this->container->get('oro_security.acl.privilege_repository');
        $aclManager = $this->container->get('oro_security.acl.manager');

        $identityString = ObjectIdentityHelper::encodeIdentityString(EntityAclExtension::NAME, Customer::class);
        $oid = $aclManager->getOid($identityString);

        $roles = $manager->getRepository(CustomerUserRole::class)->findAll();
        $extension = $aclManager->getExtensionSelector()->select($oid);
        $maskBuilders = $extension->getAllMaskBuilders();

        foreach ($roles as $role) {
            $sid = $aclManager->getSid($role);
            $aclPrivilegePermissions = $this->getPrivilegePermissions($privilegeRepository, $sid, $identityString);
            foreach ($aclPrivilegePermissions as $permission) {
                if ($permission->getAccessLevel() !== AccessLevel::SYSTEM_LEVEL) {
                    continue;
                }
                $maskName = $permission->getName() . '_LOCAL';
                foreach ($maskBuilders as $maskBuilder) {
                    if ($maskBuilder->hasMask('MASK_' . $maskName)) {
                        $maskBuilder->add($maskName);
                        $aclManager->setPermission($sid, $oid, $maskBuilder->get());
                    }
                }
            }
        }
        $aclManager->flush();
    }

    /**
     * @param AclPrivilegeRepository $privilegeRepository
     * @param SecurityIdentityInterface $sid
     * @param string $identityString
     *
     * @return AclPermission[]|array
     */
    private function getPrivilegePermissions(
        AclPrivilegeRepository $privilegeRepository,
        SecurityIdentityInterface $sid,
        $identityString
    ) {
        $allRolePrivileges = $privilegeRepository->getPrivileges($sid, 'commerce');

        foreach ($allRolePrivileges as $aclPrivilege) {
            if ($aclPrivilege->getIdentity()->getId() === $identityString) {
                return $aclPrivilege->getPermissions();
            }
        }

        return [];
    }
}
