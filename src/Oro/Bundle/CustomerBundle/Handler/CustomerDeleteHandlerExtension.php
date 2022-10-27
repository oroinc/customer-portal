<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\EntityBundle\Handler\AbstractEntityDeleteHandlerExtension;

/**
 * The delete handler extension for Customer entity.
 */
class CustomerDeleteHandlerExtension extends AbstractEntityDeleteHandlerExtension
{
    /** @var CustomerAssignHelper */
    private $customerAssignHelper;

    public function __construct(CustomerAssignHelper $customerAssignHelper)
    {
        $this->customerAssignHelper = $customerAssignHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function assertDeleteGranted($entity): void
    {
        /** @var Customer $entity */
        if ($this->customerAssignHelper->hasAssignments($entity)) {
            throw $this->createAccessDeniedException('has associations to other entities');
        }
    }
}
