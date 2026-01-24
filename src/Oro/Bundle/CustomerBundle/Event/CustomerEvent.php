<?php

namespace Oro\Bundle\CustomerBundle\Event;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a customer's group assignment changes.
 *
 * This event is triggered when a customer is assigned to or removed from a customer group,
 * allowing listeners to react to customer group changes and perform related operations.
 */
class CustomerEvent extends Event
{
    const ON_CUSTOMER_GROUP_CHANGE = 'oro_customer.customer.on_customer_group_change';

    /**
     * @var  Customer
     */
    protected $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
