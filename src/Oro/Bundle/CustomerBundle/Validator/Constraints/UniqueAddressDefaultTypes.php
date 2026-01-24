<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for validating that address types are not duplicated as defaults.
 *
 * This constraint ensures that within a collection of addresses, each address type
 * is marked as default in at most one address, preventing duplicate default type assignments.
 */
class UniqueAddressDefaultTypes extends Constraint
{
    /** @var string */
    public $message = 'Several addresses have the same default type {{ types }}.';
}
