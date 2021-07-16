<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\DataGridBundle\Extension\MassAction\DeleteMassActionHandler;

/**
 * Mass action delete handler for Customer entity. Skips the customer if it assigned to another entities.
 */
class CustomersDeleteMassActionHandler extends DeleteMassActionHandler
{
    /** @var CustomerAssignHelper */
    protected $customerAssignHelper;

    public function setCustomerAssignHelper(CustomerAssignHelper $customerAssignHelper)
    {
        $this->customerAssignHelper = $customerAssignHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function isDeleteAllowed($entity)
    {
        /** @var Customer $entity */
        if ($this->customerAssignHelper->hasAssignments($entity)) {
            return false;
        }

        return parent::isDeleteAllowed($entity);
    }
}
