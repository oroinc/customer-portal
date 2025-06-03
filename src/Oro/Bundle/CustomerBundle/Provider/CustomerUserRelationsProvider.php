<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

/**
 * Provides functionality to get a customer and a customer group for a customer user.
 */
class CustomerUserRelationsProvider
{
    private ConfigManager $configManager;
    private DoctrineHelper $doctrineHelper;

    public function __construct(ConfigManager $configManager, DoctrineHelper $doctrineHelper)
    {
        $this->configManager = $configManager;
        $this->doctrineHelper = $doctrineHelper;
    }

    public function getCustomer(?CustomerUserInterface $customerUser = null): ?Customer
    {
        return $customerUser?->getCustomer();
    }

    public function getCustomerGroup(?CustomerUserInterface $customerUser = null): ?CustomerGroup
    {
        if (null === $customerUser) {
            $anonymousGroupId = $this->configManager->get(
                Configuration::getConfigKeyByName(Configuration::ANONYMOUS_CUSTOMER_GROUP)
            );

            return $anonymousGroupId
                ? $this->doctrineHelper->getEntityReference(CustomerGroup::class, $anonymousGroupId)
                : null;
        }

        return $customerUser->getCustomer()?->getGroup();
    }

    public function getCustomerIncludingEmpty(?CustomerUserInterface $customerUser = null): ?Customer
    {
        $customer = $customerUser?->getCustomer();
        if (null === $customer) {
            $customer = new Customer();
            $customer->setGroup($this->getCustomerGroup($customerUser));
        }

        return $customer;
    }
}
