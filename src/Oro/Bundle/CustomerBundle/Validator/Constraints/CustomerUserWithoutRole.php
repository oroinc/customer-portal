<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\UserBundle\Validator\Constraints\UserWithoutRole;

class CustomerUserWithoutRole extends UserWithoutRole
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return CustomerUserWithoutRoleValidator::class;
    }
}
