<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint that checks that parent for the customer is not his child.
 */
class CircularCustomerReference extends Constraint
{
    /** @var string */
    public $message = 'oro.customer.message.circular_customer_reference';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'parent_customer_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
