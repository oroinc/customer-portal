<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for validating scope configuration with customer group and customer.
 *
 * This constraint ensures that scope configurations do not have conflicting or invalid
 * combinations of customer group and customer assignments.
 */
class ScopeWithCustomerGroupAndCustomer extends Constraint
{
    /** @var string */
    public $message = 'oro.customer.message.scope_with_customer_and_group';
}
