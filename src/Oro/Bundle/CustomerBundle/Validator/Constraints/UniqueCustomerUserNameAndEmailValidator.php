<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates uniqueness of CustomerUser email depending on guest flag
 */
class UniqueCustomerUserNameAndEmailValidator extends ConstraintValidator
{
    public function __construct(
        private CustomerUserManager $customerUserManager
    ) {
    }

    /**
     * @param CustomerUser|string $value
     * @param UniqueCustomerUserNameAndEmail $constraint
     *
     */
    #[\Override]
    public function validate($value, Constraint $constraint)
    {
        $id = null;
        if ($value instanceof CustomerUser) {
            if ($value->isGuest()) {
                return;
            }

            $id = $value->getId();
            $value = $value->getEmail();
        }

        if (!$value) {
            return;
        }

        /** @var CustomerUser $existingCustomerUser */
        $existingCustomerUser = $this->customerUserManager->findUserByEmail($value);
        if (null !== $existingCustomerUser && $existingCustomerUser->getId() !== $id) {
            $contextViolation = $this->context->buildViolation($constraint->message);
            $contextViolation
                ->atPath('email')
                ->setInvalidValue($value)
                ->setCode(UniqueCustomerUserNameAndEmail::NOT_UNIQUE_EMAIL)
                ->addViolation();
        }
    }
}
