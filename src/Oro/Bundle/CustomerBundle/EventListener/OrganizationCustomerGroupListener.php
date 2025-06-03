<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * This listener creates a new anonymous customer group for each new organization
 * and sets it as the default in the organization-level configuration.
 */
class OrganizationCustomerGroupListener
{
    private const string GROUP_NAME_NON_AUTHENTICATED = 'Non-Authenticated Visitors';

    /**
     * @var array<string, CustomerGroup>
     */
    private array $organizationCustomerGroups = [];

    public function __construct(
        private TokenAccessorInterface $tokenAccessor,
        private ?ConfigManager $configManager
    ) {
    }

    public function prePersist(OrganizationInterface $organization, PrePersistEventArgs $event): void
    {
        if ($organization->getId()) {
            return;
        }

        $entityManager = $event->getObjectManager();
        $customerGroup = new CustomerGroup();
        $customerGroup->setName(self::GROUP_NAME_NON_AUTHENTICATED);
        $customerGroup->setOwner($this->getOwner($entityManager));
        $customerGroup->setOrganization($organization);
        $entityManager->persist($customerGroup);

        // Ensure the uniqueness of the organization.
        $this->organizationCustomerGroups[spl_object_hash($organization)] = $customerGroup;
    }

    public function postFlush(): void
    {
        if (!$this->configManager) {
            return;
        }

        foreach ($this->organizationCustomerGroups as $customerGroup) {
            $organizationId = $customerGroup->getOrganization()?->getId();
            $customerGroupId = $customerGroup->getId();
            if ($organizationId && $customerGroupId) {
                $this->configManager->set($this->getConfigKey(), $customerGroupId, $organizationId);
                $this->configManager->flush($organizationId);
            }
        }

        $this->organizationCustomerGroups = [];
    }

    private function getOwner(ObjectManager $manager): ?User
    {
        $token = $this->tokenAccessor->getToken();
        $owner = $token?->getUser();
        if (!$owner) {
            $repository = $manager->getRepository(Role::class);
            $role = $repository->findOneBy(['role' => User::ROLE_ADMINISTRATOR]);
            if ($role) {
                $owner = $repository->getFirstMatchedUser($role);
            }
        }

        return $owner;
    }

    private function getConfigKey(): string
    {
        return Configuration::getConfigKeyByName(Configuration::ANONYMOUS_CUSTOMER_GROUP);
    }
}
