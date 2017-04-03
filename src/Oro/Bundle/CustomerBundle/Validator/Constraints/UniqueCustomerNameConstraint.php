<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueCustomerNameConstraint extends Constraint
{
    public $message = 'oro.customer.message.ambiguous_customer_name';
}
