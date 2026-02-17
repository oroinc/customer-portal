<?php

namespace Oro\Bundle\CustomerBundle\Acl\Voter;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Prevents removal of a customer group that is configured to be used for anonymous customers.
 */
class CustomerGroupVoter extends AbstractEntityVoter implements ServiceSubscriberInterface
{
    protected $supportedAttributes = [BasicPermission::DELETE];

    public function __construct(
        DoctrineHelper $doctrineHelper,
        private readonly ContainerInterface $container
    ) {
        parent::__construct($doctrineHelper);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            ConfigManager::class
        ];
    }

    #[\Override]
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        return $identifier && $this->isAnonymousCustomerGroup($identifier)
            ? self::ACCESS_DENIED
            : self::ACCESS_ABSTAIN;
    }

    private function isAnonymousCustomerGroup(int $identifier): bool
    {
        $organization = $this->getOrganizationByCustomerGroupById($identifier);
        $customerGroupId = $this->getConfigManager()->get(
            Configuration::getConfigKeyByName(Configuration::ANONYMOUS_CUSTOMER_GROUP),
            false,
            false,
            $organization
        );

        return $identifier === (int) $customerGroupId;
    }

    private function getConfigManager(): ConfigManager
    {
        return $this->container->get(ConfigManager::class);
    }

    private function getOrganizationByCustomerGroupById(int $identifier): OrganizationInterface
    {
        $repository = $this->doctrineHelper->getEntityRepository(CustomerGroup::class);
        $customerGroup = $repository->find($identifier);

        return $customerGroup->getOrganization();
    }
}
