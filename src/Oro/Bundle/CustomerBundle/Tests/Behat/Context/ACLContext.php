<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Oro\Bundle\SecurityBundle\Tests\Behat\Context\ACLContext as BaseACLContext;

class ACLContext extends BaseACLContext
{
    /**
     * {@inheritdoc}
     */
    protected function getRoleViewFormElement()
    {
        return $this->elementFactory->createElement('CustomerUserRoleView');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoleEditFormElement()
    {
        return $this->elementFactory->createElement('CustomerUserRoleForm');
    }
}
