<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * This constraint is used to check whether all specified enabled customer users have at least one role.
 */
class CustomerUserWithoutRole extends Constraint
{
    public string $message = 'oro.user.message.user_without_role';
}
