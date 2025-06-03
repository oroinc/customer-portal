<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

/**
 * Provide getting Customer and CustomerGroup for CustomerUser
 */
class CustomerUserRelationsProvider
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    public function __construct(ConfigManager $configManager, DoctrineHelper $doctrineHelper)
    {
        $this->configManager = $configManager;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param CustomerUserInterface|null $customerUser
     * @return null|Customer
     */
    public function getCustomer(CustomerUserInterface $customerUser = null)
    {
        return $customerUser?->getCustomer();
    }

    public function getCustomerGroup(CustomerUserInterface $customerUser = null): ?CustomerGroup
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

    /**
     * @param CustomerUserInterface|null $customerUser
     * @return null|Customer
     */
    public function getCustomerIncludingEmpty(CustomerUserInterface $customerUser = null)
    {
        $customer = $customerUser?->getCustomer();
        if (!$customer) {
            $customer = new Customer();
            $customer->setGroup($this->getCustomerGroup($customerUser));
        }

        return $customer;
    }
}
