<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class CustomerUserCheckRole extends Constraint
{
    /** @var string */
    public $message = 'oro.customer.message.user_without_role';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
