<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * It is used to block Customer User editing when we are trying to change Customer User's Customer, which leads
 * to updating Customer User's related entities (such as Orders, Quotes, Shopping Lists etc) in case the user
 * doesn't have permissions to edit these related entities
 */
class CustomerRelatedEntities extends Constraint
{
    /** @var string */
    public $message = 'oro.customer.message.no_permission_for_customer_related_entities';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return CustomerRelatedEntitiesValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
