<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\FrontendBundle\Migrations\Data\ORM\AbstractRolesData;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

class LoadCustomerUserRoles extends AbstractRolesData
{
    const ROLES_FILE_NAME = 'frontend_roles.yml';

    const ADMINISTRATOR = 'ADMINISTRATOR';
    const BUYER = 'BUYER';

    /**
     * @var Website[]
     */
    protected $websites = [];

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\WebsiteBundle\Migrations\Data\ORM\LoadWebsiteData'];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $aclManager = $this->getAclManager();
        $chainMetadataProvider = $this->container->get('oro_security.owner.metadata_provider.chain');

        $roleData = $this->loadRolesData();

        $chainMetadataProvider->startProviderEmulation(FrontendOwnershipMetadataProvider::ALIAS);

        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->findOneBy([]);
        foreach ($roleData as $roleName => $roleConfigData) {
            $role = $this->createEntity($roleName, $roleConfigData['label']);
            if (!empty($roleConfigData['website_default_role'])) {
                $this->setWebsiteDefaultRoles($role);
            }
            $role->setOrganization($organization);
            $manager->persist($role);

            $this->setUpSelfManagedData($role, $roleConfigData);

            if (!$aclManager->isAclEnabled()) {
                continue;
            }

            $sid = $aclManager->getSid($role);

            if (!empty($roleConfigData['max_permissions'])) {
                $this->setPermissionGroup($aclManager, $sid);
            }

            if (empty($roleConfigData['permissions']) || !is_array($roleConfigData['permissions'])) {
                continue;
            }

            $this->setPermissions($aclManager, $sid, $roleConfigData['permissions']);
        }

        $chainMetadataProvider->stopProviderEmulation();

        $manager->flush();
        $aclManager->flush();
    }

    /**
     * @param AclManager $aclManager
     * @param SecurityIdentityInterface $sid
     */
    protected function setPermissionGroup(AclManager $aclManager, SecurityIdentityInterface $sid)
    {
        foreach ($aclManager->getAllExtensions() as $extension) {
            $rootOid = $aclManager->getRootOid($extension->getExtensionKey());
            foreach ($extension->getAllMaskBuilders() as $maskBuilder) {
                if ($rootOid->getIdentifier() === 'entity') {
                    $fullAccessMask = $maskBuilder->getMask('GROUP_DEEP');
                } elseif ($maskBuilder->hasMask('GROUP_SYSTEM')) {
                    $fullAccessMask = $maskBuilder->getMask('GROUP_SYSTEM');
                } else {
                    $fullAccessMask = $maskBuilder->getMask('GROUP_ALL');
                }

                $aclManager->setPermission($sid, $rootOid, $fullAccessMask);
            }
        }
    }

    /**
     * @param string $name
     * @param string $label
     *
     * @return CustomerUserRole
     */
    protected function createEntity($name, $label)
    {
        $role = new CustomerUserRole(CustomerUserRole::PREFIX_ROLE . $name);
        $role->setLabel($label);

        return $role;
    }

    /**
     * @param CustomerUserRole $role
     */
    protected function setWebsiteDefaultRoles(CustomerUserRole $role)
    {
        foreach ($this->getWebsites() as $website) {
            $role->addWebsite($website);
        }
    }

    /**
     * @return Website[]
     */
    protected function getWebsites()
    {
        if (!$this->websites) {
            $websitesIterator = $this->container->get('doctrine')
                ->getManagerForClass('OroWebsiteBundle:Website')
                ->getRepository('OroWebsiteBundle:Website')
                ->getBatchIterator();

            $this->websites = iterator_to_array($websitesIterator);
        }

        return $this->websites;
    }

    /**
     * @param CustomerUserRole $role
     * @param array            $roleConfigData
     */
    private function setUpSelfManagedData(CustomerUserRole $role, array $roleConfigData)
    {
        $role->setSelfManaged(isset($roleConfigData['self_managed']) ? $roleConfigData['self_managed'] : false);
        $role->setPublic(isset($roleConfigData['public']) ? $roleConfigData['public'] : true);
    }
}
