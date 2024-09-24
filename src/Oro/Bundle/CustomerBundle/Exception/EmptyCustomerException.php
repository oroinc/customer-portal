<?php

namespace Oro\Bundle\CustomerBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

/**
 * Throws during authentication if customer user has no assigned customer.
 */
class EmptyCustomerException extends AccountStatusException
{
    #[\Override]
    public function getMessageKey(): string
    {
        return 'oro_customer.login.errors.empty_customer';
    }
}
