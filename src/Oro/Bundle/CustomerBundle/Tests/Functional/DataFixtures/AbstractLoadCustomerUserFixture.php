<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\ObjectIdentityHelper;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Owner\Metadata\ChainOwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Tests\Functional\DataFixtures\SetRolePermissionsTrait;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractLoadCustomerUserFixture extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    use ContainerAwareTrait;
    use SetRolePermissionsTrait;

    abstract protected function getCustomers(): array;

    abstract protected function getRoles(): array;

    abstract protected function getCustomerUsers(): array;

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadUser::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $this->loadRoles($manager);
        $this->loadCustomers($manager);
        $this->loadCustomerUsers();
    }

    protected function loadCustomers(ObjectManager $manager): void
    {
        $defaultUser = $this->getUser();
        foreach ($this->getCustomers() as $item) {
            $customer = new Customer();
            $customer->setName($item['name']);
            $customer->setOrganization($defaultUser->getOrganization());
            $customer->setOwner($defaultUser);
            if (isset($item['parent'])) {
                $customer->setParent($this->getReference($item['parent']));
            }
            $manager->persist($customer);
            $this->addReference($item['name'], $customer);
        }
        $manager->flush();
    }

    protected function loadRoles(ObjectManager $manager): void
    {
        /* @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');
        foreach ($this->getRoles() as $key => $items) {
            $role = new CustomerUserRole(CustomerUserRole::PREFIX_ROLE . $key);
            $role->setLabel($key);
            $manager->persist($role);

            foreach ($items as $acls) {
                $oidDescriptor = isset($acls['class'])
                    ? ObjectIdentityHelper::encodeIdentityString(EntityAclExtension::NAME, $acls['class'])
                    : $acls['oid'];
                $this->setRolePermissions($aclManager, $role, $oidDescriptor, $acls['acls']);
            }
            $this->setReference($key, $role);
        }
        $manager->flush();
        $aclManager->flush();
    }

    protected function loadCustomerUsers(): void
    {
        /* @var CustomerUserManager $userManager */
        $userManager = $this->container->get('oro_customer_user.manager');
        $defaultUser = $this->getUser();
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
                ->setOrganization($defaultUser->getOrganization())
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
    ): void {
        /* @var ChainOwnershipMetadataProvider $chainMetadataProvider */
        $chainMetadataProvider = $this->container->get('oro_security.owner.metadata_provider.chain');
        $chainMetadataProvider->startProviderEmulation(FrontendOwnershipMetadataProvider::ALIAS);
        $this->setPermissions($aclManager, $role, [$oidDescriptor => $permissions]);
        $chainMetadataProvider->stopProviderEmulation();
    }

    protected function getUser(): User
    {
        return $this->getReference(LoadUser::USER);
    }
}
