<?php

namespace Oro\Bundle\CustomerBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

class CustomerUserRegisterEvent extends Event
{
    const NAME = 'oro_customer.event.customer_register';

    /**
     * @var  CustomerUser
     */
    protected $customerUser;

    /**
     * @param CustomerUser $customerUser
     */
    public function __construct(CustomerUser $customerUser)
    {
        $this->customerUser = $customerUser;
    }

    /**
     * @return CustomerUser
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }
}
