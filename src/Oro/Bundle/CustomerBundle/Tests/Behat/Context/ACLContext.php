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
        $elementName = $this->isUserRoleEditPage() ? 'UserRoleView' : 'CustomerUserRoleView';
        return $this->elementFactory->createElement($elementName);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoleEditFormElement()
    {
        $elementName = $this->isUserRoleEditPage() ? 'UserRoleForm' : 'CustomerUserRoleForm';
        return $this->elementFactory->createElement($elementName);
    }

    /**
     * @return bool
     */
    private function isUserRoleEditPage()
    {
        return (bool) preg_match('/\/user\/role\/update\//', $this->getSession()->getCurrentUrl());
    }
}
