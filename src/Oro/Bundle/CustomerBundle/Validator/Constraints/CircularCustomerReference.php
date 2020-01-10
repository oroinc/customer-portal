<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint that checks that parent for the customer is not his child.
 */
class CircularCustomerReference extends Constraint
{
    /** @var string */
    public $messageCircular = 'oro.customer.message.circular_customer_reference';

    /** @var string */
    public $messageCircularChild = 'oro.customer.message.circular_child_customer_reference';

    /** @var string */
    public $messageItself = 'oro.customer.message.customer_reference_to_itself';

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
