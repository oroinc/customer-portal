<?php

namespace Oro\Bundle\CustomerBundle\Entity;

/**
 * Defines the contract for entities that are associated with a customer group.
 *
 * Implementing classes represent entities that have a relationship with a customer group,
 * allowing the system to organize and manage entities within customer group contexts.
 */
interface CustomerGroupAwareInterface
{
    /**
     * @return CustomerGroup
     */
    public function getCustomerGroup();

    /**
     * @param CustomerGroup $customerGroup
     * @return $this
     */
    public function setCustomerGroup(CustomerGroup $customerGroup);
}
