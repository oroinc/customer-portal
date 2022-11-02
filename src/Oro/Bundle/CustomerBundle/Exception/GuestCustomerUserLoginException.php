<?php

namespace Oro\Bundle\CustomerBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

/**
 * Throws during authentication when a guest customer user tries to log in.
 */
class GuestCustomerUserLoginException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'oro_customer.login.errors.guest';
    }
}
