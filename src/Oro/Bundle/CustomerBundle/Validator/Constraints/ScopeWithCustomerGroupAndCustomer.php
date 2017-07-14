<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ScopeWithCustomerGroupAndCustomer extends Constraint
{
    /** @var string */
    public $message = 'oro.customer.message.scope_with_customer_and_group';
}
