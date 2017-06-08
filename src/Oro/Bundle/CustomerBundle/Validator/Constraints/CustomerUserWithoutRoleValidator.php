<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Validator\UserWithoutRoleValidator;

class CustomerUserWithoutRoleValidator extends UserWithoutRoleValidator
{
    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    protected function isUserValid(UserInterface $user)
    {
        return !$user->isEnabled() || $user->getRoles();
    }
}
