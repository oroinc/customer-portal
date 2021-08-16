<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Oro\Bundle\SecurityBundle\Tests\Behat\Context\ACLContext as BaseACLContext;

class ACLContext extends BaseACLContext
{
    /**
     * {@inheritdoc}
     */
    protected function getRoleEditFormElement()
    {
        $elementName = $this->isUserRoleEditPage() || $this->isUserRoleCreatePage() ?
            'UserRoleForm' : 'CustomerUserRoleForm';

        return $this->elementFactory->createElement($elementName);
    }

    /**
     * @return bool
     */
    private function isUserRoleEditPage()
    {
        return (bool) preg_match(
            '/\\'.$this->getAppContainer()->getParameter('web_backend_prefix').'\/user\/role\/update\//',
            $this->getSession()->getCurrentUrl()
        );
    }

    /**
     * @return bool
     */
    private function isUserRoleCreatePage()
    {
        return (bool) preg_match(
            '/\\'.$this->getAppContainer()->getParameter('web_backend_prefix').'\/user\/role\/create/',
            $this->getSession()->getCurrentUrl()
        );
    }
}
