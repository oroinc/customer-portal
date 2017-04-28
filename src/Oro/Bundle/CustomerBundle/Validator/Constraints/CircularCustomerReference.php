<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class CircularCustomerReference extends Constraint
{
    /** @var string */
    public $message = 'oro.customer.message.circular_customer_reference';
}
