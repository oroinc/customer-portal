<?php

namespace Oro\Bundle\CustomerBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

/**
 * 'Customer User is Guest.' exception message holder
 */
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
