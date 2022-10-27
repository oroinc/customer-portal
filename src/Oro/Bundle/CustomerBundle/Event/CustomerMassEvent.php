<?php

namespace Oro\Bundle\CustomerBundle\Event;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event class to pass updated customers.
 */
class CustomerMassEvent extends Event
{
    public const ON_CUSTOMER_GROUP_MASS_CHANGE = 'oro_customer.customer.on_customer_group_mass_change';

    /**
     * @var Customer[]
     */
    protected $customers = [];

    /**
     * @param array|Customer $customers
     */
    public function __construct(array $customers = [])
    {
        $this->customers = $customers;
    }

    /**
     * @return Customer[]|array
     */
    public function getCustomers()
    {
        return $this->customers;
    }
}
