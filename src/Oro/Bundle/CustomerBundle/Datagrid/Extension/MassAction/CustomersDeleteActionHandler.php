<?php

namespace Oro\Bundle\CustomerBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Extension\MassAction\DeleteMassActionHandler;

class CustomersDeleteActionHandler extends DeleteMassActionHandler
{
    /**
     * {@inheritdoc}
     */
    protected function isDeleteAllowed($entity)
    {
        /** @var CustomerUser $entity */
        if ($this->securityFacade->getLoggedUserId() === $entity->getId()) {
            return false;
        }

        return parent::isDeleteAllowed($entity);
    }
}
