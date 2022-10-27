<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that all specified enabled customer users have at least one role.
 */
class CustomerUserWithoutRoleValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CustomerUserWithoutRole) {
            throw new UnexpectedTypeException($constraint, CustomerUserWithoutRole::class);
        }

        if (!$value instanceof Collection) {
            throw new UnexpectedTypeException($value, Collection::class);
        }

        if ($value->isEmpty()) {
            return;
        }

        $invalidUserNames = [];
        foreach ($value as $user) {
            if (!$user instanceof CustomerUser) {
                throw new UnexpectedTypeException($user, CustomerUser::class);
            }
            if ($user->isEnabled() && !$user->getRoles()) {
                $invalidUserNames[] = $user->getFullName();
            }
        }

        if ($invalidUserNames) {
            $this->context->addViolation(
                $constraint->message,
                ['{{ userName }}' => implode(', ', $invalidUserNames)]
            );
        }
    }
}
