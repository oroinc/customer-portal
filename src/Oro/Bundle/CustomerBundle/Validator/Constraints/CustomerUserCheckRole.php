<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for validating that enabled customer users have at least one role assigned.
 *
 * This constraint ensures that customer users who are enabled in the system have
 * appropriate roles assigned to them, preventing the creation of enabled users without
 * any permissions or role assignments.
 */
class CustomerUserCheckRole extends Constraint
{
    /** @var string */
    public $message = 'oro.customer.message.user_without_role';

    #[\Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
