<?php

namespace Oro\Bundle\CustomerBundle\Entity;

/**
 * Defines the contract for entities that are associated with a customer.
 *
 * Implementing classes represent entities that have a relationship with a customer,
 * allowing the system to organize and manage entities within customer contexts.
 */
interface CustomerAwareInterface
{
    /**
     * @return Customer
     */
    public function getCustomer();

    /**
     * @param Customer $account
     * @return $this
     */
    public function setCustomer(Customer $account);
}
