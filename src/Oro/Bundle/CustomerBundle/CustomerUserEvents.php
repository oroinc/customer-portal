<?php

namespace Oro\Bundle\CustomerBundle;

/**
 * Represents CustomerUser registration events
 */
class CustomerUserEvents
{
    /**
     * The REGISTRATION_COMPLETED event occurs after saving the user in the registration process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("Oro\Bundle\CustomerBundle\Event\FilterCustomerUserResponseEvent")
     */
    const REGISTRATION_COMPLETED = 'customer_user.registration.completed';

    /**
     * The REGISTRATION_CONFIRMED event occurs after confirming the account.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("Oro\Bundle\CustomerBundle\Event\FilterCustomerUserResponseEvent")
     */
    const REGISTRATION_CONFIRMED = 'customer_user.registration.confirmed';
}
