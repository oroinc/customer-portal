<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Owner\Metadata\ChainOwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Tests\Functional\DataFixtures\SetRolePermissionsTrait;
use Oro\Bundle\UserBundle\Entity\Repository\RoleRepository;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractLoadCustomerUserFixture extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use SetRolePermissionsTrait;

    /** @return array */
    abstract protected function getCustomers();

    /** @return array */
    abstract protected function getRoles();

    /** @return array */
    abstract protected function getCustomerUsers();

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadRoles($manager);
        $this->loadCustomers($manager);
        $this->loadCustomerUsers($manager);
    }

    protected function loadCustomers(ObjectManager $manager)
    {
        $defaultUser = $this->getUser($manager);
        $organization = $defaultUser->getOrganization();

        foreach ($this->getCustomers() as $item) {
            $customer = new Customer();
            $customer
                ->setName($item['name'])
                ->setOrganization($organization)
                ->setOwner($defaultUser);
            if (isset($item['parent'])) {
                $customer->setParent($this->getReference($item['parent']));
            }
            $manager->persist($customer);

            $this->addReference($item['name'], $customer);
        }

        $manager->flush();
    }

    protected function loadRoles(ObjectManager $manager)
    {
        /* @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');

        foreach ($this->getRoles() as $key => $items) {
            $role = new CustomerUserRole(CustomerUserRole::PREFIX_ROLE . $key);
            $role->setLabel($key);
            $manager->persist($role);

            foreach ($items as $acls) {
                $oidDescriptor = isset($acls['class'])
                    ? 'entity:' . $acls['class']
                    : $acls['oid'];
                $this->setRolePermissions($aclManager, $role, $oidDescriptor, $acls['acls']);
            }

            $this->setReference($key, $role);
        }

        $manager->flush();
        $aclManager->flush();
    }

    protected function loadCustomerUsers(ObjectManager $manager)
    {
        /* @var CustomerUserManager $userManager */
        $userManager = $this->container->get('oro_customer_user.manager');

        $defaultUser = $this->getUser($manager);
        $organization = $defaultUser->getOrganization();

        foreach ($this->getCustomerUsers() as $item) {
            /* @var CustomerUser $customerUser */
            $customerUser = $userManager->createUser();

            $customerUser
                ->setEmail($item['email'])
                ->setCustomer($this->getReference($item['customer']))
                ->setOwner($defaultUser)
                ->setFirstName($item['firstname'])
                ->setLastName($item['lastname'])
                ->setConfirmed(true)
                ->setOrganization($organization)
                ->addUserRole($this->getReference($item['role']))
                ->setSalt('')
                ->setPlainPassword($item['password'])
                ->setEnabled(true);

            $userManager->updateUser($customerUser);

            $this->setReference($item['email'], $customerUser);
        }
    }

    /**
     * @param AclManager       $aclManager
     * @param CustomerUserRole $role
     * @param string           $oidDescriptor
     * @param string[]         $permissions
     */
    protected function setRolePermissions(
        AclManager $aclManager,
        CustomerUserRole $role,
        string $oidDescriptor,
        array $permissions
    ) {
        /* @var ChainOwnershipMetadataProvider $chainMetadataProvider */
        $chainMetadataProvider = $this->container->get('oro_security.owner.metadata_provider.chain');
        $chainMetadataProvider->startProviderEmulation(FrontendOwnershipMetadataProvider::ALIAS);

        $this->setPermissions(
            $aclManager,
            $role,
            [$oidDescriptor => $permissions]
        );

        $chainMetadataProvider->stopProviderEmulation();
    }

    /**
     * @param ObjectManager $manager
     * @return User
     */
    protected function getUser(ObjectManager $manager)
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $manager->getRepository(Role::class);
        /** @var Role $role */
        $role = $roleRepository->findOneBy(['role' => LoadRolesData::ROLE_ADMINISTRATOR]);

        if (!$role) {
            throw new \RuntimeException(sprintf('%s role should exist.', LoadRolesData::ROLE_ADMINISTRATOR));
        }

        $user = $roleRepository->getFirstMatchedUser($role);

        if (!$user) {
            throw new \RuntimeException(
                sprintf('At least one user with role %s should exist.', LoadRolesData::ROLE_ADMINISTRATOR)
            );
        }

        return $user;
    }
}
