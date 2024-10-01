<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validates uniqueness of CustomerUser email depending on guest flag
 */
class UniqueCustomerUserNameAndEmail extends Constraint
{
    public const NOT_UNIQUE_EMAIL = 'not_unique_email';

    public $message = 'oro.customer.message.user_customer_exists';

    #[\Override]
    public function validatedBy(): string
    {
        return 'oro_customer.customer_user.validator.unique_name_and_email';
    }

    #[\Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
