<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueCustomerUserNameAndEmail extends Constraint
{
    public $message = 'oro.customer.message.user_customer_exists';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'oro_customer.customer_user.validator.unique_name_and_email';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
