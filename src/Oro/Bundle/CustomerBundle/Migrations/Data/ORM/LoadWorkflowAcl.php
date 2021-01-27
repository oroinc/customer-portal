<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;
use Oro\Bundle\WorkflowBundle\Acl\Extension\WorkflowAclExtension;
use Oro\Bundle\WorkflowBundle\Acl\Extension\WorkflowMaskBuilder;

/**
 * Sets Corporate access level to the root ACE for workflows for all storefront roles.
 */
class LoadWorkflowAcl extends AbstractUpdatePermissions implements DependentFixtureInterface, VersionedFixtureInterface
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
    public function getVersion()
    {
        return '1.1';
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

        $roles = $this->getRoles($manager);
        $rootOid = $aclManager->getRootOid(WorkflowAclExtension::NAME);
        foreach ($roles as $role) {
            $sid = $aclManager->getSid($role);
            $aclManager->setPermission($sid, $rootOid, WorkflowMaskBuilder::GROUP_DEEP, true);
        }
        $aclManager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @return CustomerUserRole[]
     */
    private function getRoles(ObjectManager $manager)
    {
        return $manager->getRepository(CustomerUserRole::class)->findAll();
    }
}
