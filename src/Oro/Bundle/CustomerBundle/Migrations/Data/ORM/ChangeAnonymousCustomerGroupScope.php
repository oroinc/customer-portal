<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Removes the anonymous customer group configuration from the application level, as it has been
 * moved to the organization level.
 */
class ChangeAnonymousCustomerGroupScope extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    private ?ContainerInterface $container = null;

    public function setContainer(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadOrganizationAndBusinessUnitData::class, LoadAnonymousCustomerGroup::class];
    }

    public function load(ObjectManager $manager): void
    {
        $organizations = $this->getOrganizations($manager);
        /** @var OrganizationInterface $organization */
        foreach ($organizations as $organization) {
            $customerGroup = $this->createCustomerGroupIfNotExists($manager, $organization);
            $this->updateConfigWithCustomerGroup($customerGroup, $organization);
        }
    }

    private function createCustomerGroupIfNotExists(
        ObjectManager $manager,
        OrganizationInterface $organization
    ): CustomerGroup {
        $customerGroup = $this->findCustomerGroup($manager, $organization);
        if (!$customerGroup) {
            $customerGroup = new CustomerGroup();
            $customerGroup->setName(LoadAnonymousCustomerGroup::GROUP_NAME_NON_AUTHENTICATED);
            $customerGroup->setOrganization($organization);
        }

        $customerGroup->setOwner($this->getOwner($manager));
        $manager->persist($customerGroup);
        $manager->flush();

        return $customerGroup;
    }

    private function findCustomerGroup(
        ObjectManager $manager,
        OrganizationInterface $organization
    ): ?CustomerGroup {
        $repository = $manager->getRepository(CustomerGroup::class);

        return $repository->findOneBy([
            'organization' => $organization,
            'name' => LoadAnonymousCustomerGroup::GROUP_NAME_NON_AUTHENTICATED
        ]);
    }

    private function getOwner(ObjectManager $manager): ?User
    {
        $repository = $manager->getRepository(Role::class);
        $role = $repository->findOneBy(['role' => LoadRolesData::ROLE_ADMINISTRATOR]);

        return $repository->getFirstMatchedUser($role);
    }

    private function updateConfigWithCustomerGroup(
        CustomerGroup $customerGroup,
        OrganizationInterface $organization
    ): void {
        if ($this->container->has('oro_config.organization')) {
            $configManager = $this->container->get('oro_config.organization');
            $configManager->set($this->getConfigurationKey(), $customerGroup->getId(), $organization->getId());
            $configManager->flush();
        }
    }

    private function getOrganizations(ObjectManager $manager): array
    {
        return $manager->getRepository(Organization::class)->findAll();
    }

    private function getConfigurationKey(): string
    {
        return Configuration::getConfigKeyByName(Configuration::ANONYMOUS_CUSTOMER_GROUP);
    }
}
