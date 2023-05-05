<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\FrontendBundle\Migrations\Data\ORM\AbstractRolesData;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Owner\Metadata\ChainOwnershipMetadataProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * Creates default storefront roles.
 */
class LoadCustomerUserRoles extends AbstractRolesData
{
    public const ROLES_FILE_NAME = 'frontend_roles.yml';
    public const ADMINISTRATOR = 'ADMINISTRATOR';
    public const BUYER = 'BUYER';
    public const WEBSITE_DEFAULT_ROLE = 'website_default_role';
    public const WEBSITE_GUEST_ROLE = 'website_guest_role';

    /** @var Website[] */
    protected $websites = [];

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [LoadAnonymousCustomerGroup::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $aclManager = $this->getAclManager();

        $organization = $this->getOrganization($manager);
        $roleData = $this->loadRolesData();

        /* @var ChainOwnershipMetadataProvider $chainMetadataProvider */
        $chainMetadataProvider = $this->container->get('oro_security.owner.metadata_provider.chain');
        $chainMetadataProvider->startProviderEmulation(FrontendOwnershipMetadataProvider::ALIAS);

        foreach ($roleData as $roleName => $roleConfigData) {
            $role = $this->createEntity($roleName, $roleConfigData['label']);
            $role->setOrganization($organization);
            if (!empty($roleConfigData['website_default_role'])) {
                $this->setReference(self::WEBSITE_DEFAULT_ROLE, $role);
                $this->setWebsiteDefaultRoles($role);
            }
            if (!empty($roleConfigData['website_guest_role'])) {
                $this->setReference(self::WEBSITE_GUEST_ROLE, $role);
                $this->setWebsiteGuestRoles($role);
            }
            $manager->persist($role);

            $this->setUpSelfManagedData($role, $roleConfigData);

            if ($aclManager->isAclEnabled()) {
                $sid = $aclManager->getSid($role);
                if (!empty($roleConfigData['max_permissions'])) {
                    $this->setPermissionGroup($aclManager, $sid);
                }
                $this->setPermissions($aclManager, $sid, $roleConfigData['permissions'] ?? []);
            }
        }

        $chainMetadataProvider->stopProviderEmulation();

        $manager->flush();
        if ($aclManager->isAclEnabled()) {
            $aclManager->flush();
        }
    }

    protected function setPermissionGroup(AclManager $aclManager, SecurityIdentityInterface $sid)
    {
        foreach ($aclManager->getAllExtensions() as $extension) {
            $rootOid = $aclManager->getRootOid($extension->getExtensionKey());
            foreach ($extension->getAllMaskBuilders() as $maskBuilder) {
                if ($rootOid->getIdentifier() === 'entity') {
                    $mask = $maskBuilder->getMaskForGroup('DEEP');
                } elseif ($maskBuilder->hasMaskForGroup('SYSTEM')) {
                    $mask = $maskBuilder->getMaskForGroup('SYSTEM');
                } else {
                    $mask = $maskBuilder->getMaskForGroup('ALL');
                }
                $aclManager->setPermission($sid, $rootOid, $mask);
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

    protected function setWebsiteDefaultRoles(CustomerUserRole $role)
    {
        foreach ($this->getWebsites($role->getOrganization()) as $website) {
            $website->setDefaultRole($role);
        }
    }

    protected function setWebsiteGuestRoles(CustomerUserRole $role)
    {
        foreach ($this->getWebsites($role->getOrganization()) as $website) {
            $website->setGuestRole($role);
        }
    }

    /**
     * @param Organization $organization
     * @return Website[]
     */
    protected function getWebsites(Organization $organization)
    {
        if (!$this->websites) {
            $this->websites = $this->container->get('doctrine')
                ->getManagerForClass(Website::class)
                ->getRepository(Website::class)
                ->findBy(['organization' => $organization]);
        }

        return $this->websites;
    }

    protected function setUpSelfManagedData(CustomerUserRole $role, array $roleConfigData)
    {
        $role->setSelfManaged($roleConfigData['self_managed'] ?? false);
        $role->setPublic($roleConfigData['public'] ?? true);
    }

    /**
     * @param ObjectManager $manager
     * @return object|Organization|null
     */
    protected function getOrganization(ObjectManager $manager)
    {
        return $manager->getRepository(Organization::class)->findOneBy([]);
    }
}
