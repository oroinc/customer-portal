<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\OrganizationBundle\Migrations\Data\Demo\ORM\LoadAcmeOrganizationAndBusinessUnitData;

/**
 * Loads demo CustomerUserRoles
 */
class LoadAcmeCustomerUserRoles extends LoadCustomerUserRoles
{
    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            LoadAcmeOrganizationAndBusinessUnitData::class
        ];
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

        $organization = $this->getReference(LoadAcmeOrganizationAndBusinessUnitData::REFERENCE_DEMO_ORGANIZATION);
        foreach ($roleData as $roleName => $roleConfigData) {
            $role = $this->createEntity($roleName, $roleConfigData['label']);
            if (!empty($roleConfigData['website_default_role'])) {
                $this->setWebsiteDefaultRoles($role);
            }
            if (!empty($roleConfigData['website_guest_role'])) {
                $this->setWebsiteGuestRoles($role);
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
     * {@inheritdoc}
     */
    protected function getFileName($bundle)
    {
        return sprintf('@%s%s%s', $bundle, '/Migrations/Data/Demo/ORM/data/', 'acme_frontend_roles.yml');
    }
}
