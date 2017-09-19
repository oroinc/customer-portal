<?php

namespace Oro\Bundle\CustomerBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class GuestCustomerUserLoginException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Customer User is Guest.';
    }
}
