<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\OrganizationBundle\Migrations\Data\Demo\ORM\LoadAcmeOrganizationAndBusinessUnitData;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\WorkflowBundle\Acl\Extension\WorkflowAclExtension;
use Oro\Bundle\WorkflowBundle\Acl\Extension\WorkflowMaskBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This fixture adds root permissions to all existing acme frontend roles same as in LoadWorkflowAcl.
 */
class LoadWorkflowAcl extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadAcmeCustomerUserRoles::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var AclManager $manager */
        $securityManager = $this->container->get('oro_security.acl.manager');

        if (!$securityManager->isAclEnabled()) {
            return;
        }

        $roles = $this->getRoles($manager);
        $rootOid = $securityManager->getRootOid(WorkflowAclExtension::NAME);
        foreach ($roles as $role) {
            $sid = $securityManager->getSid($role);
            $securityManager->setPermission($sid, $rootOid, WorkflowMaskBuilder::GROUP_SYSTEM, true);
        }

        $securityManager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @return CustomerUserRole[]
     */
    protected function getRoles(ObjectManager $manager)
    {
        $acmeOrganization = $this->getReference(LoadAcmeOrganizationAndBusinessUnitData::REFERENCE_DEMO_ORGANIZATION);

        return $manager
            ->getRepository('OroCustomerBundle:CustomerUserRole')
            ->findBy(['organization' => $acmeOrganization]);
    }
}
