<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SoapBundle\Handler\DeleteHandler;

/**
 * Delete handler for Customer entity. Disallows to delete the customer if it assigned to another entities.
 */
class CustomerDeleteHandler extends DeleteHandler
{
    /** @var CustomerAssignHelper */
    protected $customerAssignHelper;

    /**
     * @param CustomerAssignHelper $customerAssignHelper
     */
    public function setCustomerAssignHelper(CustomerAssignHelper $customerAssignHelper)
    {
        $this->customerAssignHelper = $customerAssignHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
        /** @var Customer $entity */
        if ($this->customerAssignHelper->hasAssignments($entity)) {
            throw new ForbiddenException(
                'This customer has associated with other entities.'
            );
        }
    }
}
