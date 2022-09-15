<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\ScopeBundle\Manager\ScopeCriteriaProviderInterface;

/**
 * The scope criteria provider for the anonymous customer group.
 */
class ScopeAnonymousCustomerGroupCriteriaProvider implements ScopeCriteriaProviderInterface
{
    private CustomerUserRelationsProvider $customerUserRelationsProvider;

    public function __construct(CustomerUserRelationsProvider $customerUserRelationsProvider)
    {
        $this->customerUserRelationsProvider = $customerUserRelationsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getCriteriaField()
    {
        return ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP;
    }

    /**
     * {@inheritdoc}
     */
    public function getCriteriaValue(): ?CustomerGroup
    {
        return $this->customerUserRelationsProvider->getCustomerGroup();
    }

    /**
     * {@inheritdoc}
     */
    public function getCriteriaValueType()
    {
        return CustomerGroup::class;
    }
}
