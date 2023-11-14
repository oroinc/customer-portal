<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Acl\Cache\CustomerVisitorAclCache;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\SecurityBundle\Owner\Metadata\ChainOwnershipMetadataProvider;
use Oro\Bundle\UserBundle\Entity\AbstractRole;
use Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Abstract logic for Customer User Role handling.
 */
abstract class AbstractCustomerUserRoleHandler extends AclRoleHandler
{
    protected RequestStack $requestStack;
    protected ConfigProvider $ownershipConfigProvider;
    protected ChainOwnershipMetadataProvider $chainMetadataProvider;
    protected DoctrineHelper $doctrineHelper;
    private CustomerVisitorAclCache $visitorAclCache;
    protected ?Customer $originalCustomer = null;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function setOwnershipConfigProvider(ConfigProvider $provider)
    {
        $this->ownershipConfigProvider = $provider;
    }

    public function setChainMetadataProvider(ChainOwnershipMetadataProvider $chainMetadataProvider)
    {
        $this->chainMetadataProvider = $chainMetadataProvider;
    }

    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    public function setVisitorAclCache(CustomerVisitorAclCache $visitorAclCache): void
    {
        $this->visitorAclCache = $visitorAclCache;
    }

    /**
     * {@inheritdoc}
     */
    protected function createRoleFormInstance(AbstractRole $role, array $privilegeConfig)
    {
        return $this->formFactory->create(
            CustomerUserRoleType::class,
            $role,
            ['privilege_config' => $privilegeConfig]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function filterPrivileges(ArrayCollection $privileges, array $rootIds)
    {
        $privileges = parent::filterPrivileges($privileges, $rootIds);

        $entityPrefix = 'entity:';

        foreach ($privileges as $key => $privilege) {
            $oid = $privilege->getIdentity()->getId();
            if (str_starts_with($oid, $entityPrefix)) {
                $className = substr($oid, \strlen($entityPrefix));
                if (!$this->ownershipConfigProvider->hasConfig($className)) {
                    unset($privileges[$key]);
                }
            }
        }

        return $privileges;
    }

    /**
     * {@inheritDoc}
     */
    protected function getAclGroup()
    {
        return CustomerUser::SECURITY_GROUP;
    }

    /**
     * @param CustomerUserRole $role
     * @return ArrayCollection[]
     */
    public function getCustomerUserRolePrivileges(CustomerUserRole $role)
    {
        $sortedPrivileges= [];
        $privileges = $this->getRolePrivileges($role);

        $this->loadPrivilegeConfigPermissions(true);

        foreach ($this->privilegeConfig as $fieldName => $config) {
            $sortedPrivileges[$fieldName] = $this->filterPrivileges($privileges, $config['types']);
            $this->applyOptions($sortedPrivileges[$fieldName], $config);
        }

        return $sortedPrivileges;
    }

    /**
     * @param CustomerUserRole $role
     * @return array
     */
    public function getCustomerUserRolePrivilegeConfig(CustomerUserRole $role)
    {
        return $this->privilegeConfig;
    }

    /**
     * @param CustomerUserRole|AbstractRole $role
     * @param array $appendUsers
     * @param array $removeUsers
     */
    protected function applyCustomerLimits(CustomerUserRole $role, array &$appendUsers, array &$removeUsers)
    {
        /** @var CustomerUserRoleRepository $roleRepository */
        $roleRepository = $this->doctrineHelper->getEntityRepository($role);

        // Role moved to another customer OR customer added
        if ($role->getId() && (
            ($this->originalCustomer !== $role->getCustomer() &&
                    $this->originalCustomer !== null && $role->getCustomer() !== null) ||
                ($this->originalCustomer === null && $role->getCustomer() !== null)
        )
        ) {
            // Remove assigned users
            $assignedUsers = $roleRepository->getAssignedUsers($role);

            $removeUsers = array_replace(
                $removeUsers,
                array_filter(
                    $assignedUsers,
                    function (CustomerUser $customerUser) use ($role) {
                        return $customerUser->getCustomer() !== $role->getCustomer();
                    }
                )
            );

            $appendNewUsers = array_diff($appendUsers, $removeUsers);
            $removeNewUsers = array_diff($removeUsers, $appendUsers);

            $removeUsers = $removeNewUsers;
            $appendUsers = $appendNewUsers;
        }

        if ($role->getCustomer()) {
            // Security check
            $appendUsers = array_filter(
                $appendUsers,
                function (CustomerUser $user) use ($role) {
                    return $user->getCustomer() === $role->getCustomer();
                }
            );
        }
    }

    protected function clearAclCache(AbstractRole $role): void
    {
        if ($role instanceof CustomerUserRole) {
            $anonymousRoleWebsiteIds = $this->doctrineHelper->createQueryBuilder(Website::class, 'w')
                ->where('w.guest_role = :role')
                ->setParameter('role', $role)
                ->getQuery()
                ->getArrayResult();

            foreach ($anonymousRoleWebsiteIds as $websiteId) {
                $this->visitorAclCache->clearWebsiteData($websiteId['id']);
            }

            $this->aclCache->clearCache();

            $customers = [];
            foreach ($role->getCustomerUsers() as $customerUser) {
                $customers[] = $customerUser->getCustomer()->getId();
            }
            $customers = array_unique($customers);
            if (count($customers)) {
                $this->queryCacheProvider->clearForEntities(Customer::class, $customers);
            }
            return;
        }

        parent::clearAclCache($role);
    }
}
