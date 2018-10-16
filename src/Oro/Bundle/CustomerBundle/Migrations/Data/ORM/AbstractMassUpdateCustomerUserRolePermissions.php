<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractMassUpdateCustomerUserRolePermissions extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    use ContainerAwareTrait;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

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
        $this->objectManager = $manager;

        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');

        if ($aclManager->isAclEnabled()) {
            $this->updateRole($aclManager);
            $aclManager->flush();
        }
    }

    /**
     * @param AclManager $manager
     */
    protected function updateRole(AclManager $manager)
    {
        foreach ($this->getACLData() as $roleName => $aclSettings) {
            $role = $this->objectManager
                ->getRepository(CustomerUserRole::class)
                ->findOneBy(['role' => $roleName]);

            if ($role) {
                foreach ($aclSettings as $entityOid => $permissions) {
                    $sid = $manager->getSid($role);
                    $oid = $manager->getOid($entityOid);

                    $extension = $manager->getExtensionSelector()->select($oid);
                    $maskBuilders = $extension->getAllMaskBuilders();

                    foreach ($maskBuilders as $maskBuilder) {
                        foreach ($permissions as $permission) {
                            if ($maskBuilder->hasMask('MASK_' . $permission)) {
                                $maskBuilder->add($permission);
                            }
                        }

                        $manager->setPermission($sid, $oid, $maskBuilder->get());
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    abstract protected function getACLData(): array;
}
