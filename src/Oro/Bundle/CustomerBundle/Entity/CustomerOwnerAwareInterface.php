<?php

namespace Oro\Bundle\CustomerBundle\Entity;

/**
 * Defines the contract for entities that are owned by a customer or customer user.
 *
 * Implementing classes represent entities that have ownership relationships with both
 * customers and customer users, allowing the system to track and manage entity ownership.
 */
interface CustomerOwnerAwareInterface
{
    /**
     * @return \Oro\Bundle\CustomerBundle\Entity\Customer
     */
    public function getCustomer();

    /**
     * @return \Oro\Bundle\CustomerBundle\Entity\CustomerUser
     */
    public function getCustomerUser();
}
