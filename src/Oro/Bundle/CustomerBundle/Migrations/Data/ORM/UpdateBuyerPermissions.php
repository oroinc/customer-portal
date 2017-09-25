<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\FrontendBundle\Migrations\Data\ORM\LoadUserRolesData;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateBuyerPermissions extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadUserRolesData::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');

        if ($aclManager->isAclEnabled()) {
            $this->updateRole($manager, $aclManager);
            $aclManager->flush();
        }
    }

    /**
     * @param ObjectManager $objectManager
     * @param AclManager $aclManager
     */
    protected function updateRole(ObjectManager $objectManager, AclManager $aclManager)
    {
        $role = $objectManager->getRepository('OroCustomerBundle:CustomerUserRole')
            ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER']);

        if ($role) {
            $this->changeCustomerUserPermissions($aclManager, $role);
        }
    }

    /**
     * @param AclManager $manager
     * @param CustomerUserRole $role
     */
    protected function changeCustomerUserPermissions(AclManager $manager, CustomerUserRole $role)
    {
        $sid = $manager->getSid($role);
        $oid = $manager->getOid('entity:Oro\Bundle\CustomerBundle\Entity\CustomerUser');

        $extension = $manager->getExtensionSelector()->select($oid);
        $maskBuilders = $extension->getAllMaskBuilders();

        $permission = 'VIEW_LOCAL';
        foreach ($maskBuilders as $maskBuilder) {
            if ($maskBuilder->hasMask('MASK_' . $permission)) {
                $maskBuilder->add($permission);
            }

            $manager->setPermission($sid, $oid, $maskBuilder->get());
        }
    }
}
