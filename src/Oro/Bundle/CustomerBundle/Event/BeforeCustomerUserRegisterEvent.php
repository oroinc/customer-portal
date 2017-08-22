<?php

namespace Oro\Bundle\CustomerBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Fires before customer user form processed and gives possibility to overwrite standard redirect behaviour after
 * registration complete
 */
class BeforeCustomerUserRegisterEvent extends Event
{
    const NAME = 'oro_customer.event.before_customer_user_register';

    /**
     * @var array|callable
     */
    private $redirect;

    /**
     * @return array|callable
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * @param $redirect array|callable
     * @return $this
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;

        return $this;
    }
}
