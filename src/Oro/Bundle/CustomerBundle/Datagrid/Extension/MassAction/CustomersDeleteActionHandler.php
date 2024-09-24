<?php

namespace Oro\Bundle\CustomerBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Extension\MassAction\DeleteMassActionHandler;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Delete mass action handler for CustomerUser entity for the storefront. Skips the current logged-in customer user.
 */
class CustomersDeleteActionHandler extends DeleteMassActionHandler
{
    private TokenAccessorInterface $tokenAccessor;

    public function setTokenAccessor(TokenAccessorInterface $tokenAccessor): void
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    #[\Override]
    protected function isDeleteAllowed(object $entity): bool
    {
        /** @var CustomerUser $entity */
        if ($this->tokenAccessor->getUserId() === $entity->getId()) {
            return false;
        }

        return parent::isDeleteAllowed($entity);
    }
}
