<?php

namespace Oro\Bundle\CustomerBundle\Action;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\Action\Action\AssignActiveUser;
use Oro\Component\Action\Exception\ActionException;

/**
 * Returns active Visitor instance (which represents guest)
 */
class GetActiveVisitor extends AssignActiveUser
{
    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $visitor = null;

        $token = $this->tokenStorage->getToken();
        if ($token instanceof AnonymousCustomerUserToken) {
            $visitor = $token->getVisitor();
        }

        if (!$visitor && $this->options['exceptionOnNotFound']) {
            throw new ActionException('Can\'t extract active visitor');
        }

        $this->contextAccessor->setValue($context, $this->options['attribute'], $visitor);
    }
}
