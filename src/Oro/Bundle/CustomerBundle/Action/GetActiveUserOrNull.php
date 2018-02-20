<?php

namespace Oro\Bundle\CustomerBundle\Action;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\Action\Action\AssignActiveUser;

class GetActiveUserOrNull extends AssignActiveUser
{
    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof AnonymousCustomerUserToken) {
            $customerUser = $token->getVisitor()->getCustomerUser();
            $this->contextAccessor->setValue($context, $this->options['attribute'], $customerUser);
        } else {
            parent::executeAction($context);
        }
    }
}
