<?php

namespace Oro\Bundle\CustomerBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Extension\MassAction\DeleteMassActionHandler;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class CustomersDeleteActionHandler extends DeleteMassActionHandler
{
    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    public function setTokenAccessor(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function isDeleteAllowed($entity)
    {
        /** @var CustomerUser $entity */
        if ($this->tokenAccessor->getUserId() === $entity->getId()) {
            return false;
        }

        return parent::isDeleteAllowed($entity);
    }
}
