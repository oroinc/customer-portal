<?php

namespace Oro\Bundle\CustomerBundle\Action;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\Action\Action\AssignActiveUser;

/**
 * Set active customer user to attribute.
 */
class GetActiveUserOrNull extends AssignActiveUser
{
    #[\Override]
    protected function executeAction($context)
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof AnonymousCustomerUserToken) {
            $customerUser = $token->getVisitor()?->getCustomerUser();
            $this->contextAccessor->setValue($context, $this->options['attribute'], $customerUser);
        } else {
            parent::executeAction($context);
        }
    }
}
