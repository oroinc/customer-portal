<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Tests\Behat\Context\ACLContext as BaseACLContext;

class ACLContext extends BaseACLContext
{
    private string $webBackendPrefix;

    public function __construct(DoctrineHelper $doctrineHelper, string $webBackendPrefix)
    {
        $this->webBackendPrefix = $webBackendPrefix;

        parent::__construct($doctrineHelper);
    }

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
            '/\\'.$this->webBackendPrefix.'\/user\/role\/update\//',
            $this->getSession()->getCurrentUrl()
        );
    }

    /**
     * @return bool
     */
    private function isUserRoleCreatePage()
    {
        return (bool) preg_match(
            '/\\'.$this->webBackendPrefix.'\/user\/role\/create/',
            $this->getSession()->getCurrentUrl()
        );
    }
}
