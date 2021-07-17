<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates uniqness of CustomerUser email depending on guest flag
 */
class UniqueCustomerUserNameAndEmailValidator extends ConstraintValidator
{
    /**
     * @var CustomerUserManager
     */
    private $customerUserManager;

    public function __construct(CustomerUserManager $customerUserManager)
    {
        $this->customerUserManager = $customerUserManager;
    }

    /**
     * @param CustomerUser|string $value
     * @param UniqueCustomerUserNameAndEmail $constraint
     *
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $id = false;
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
            $this->context->buildViolation($constraint->message)
                ->atPath('email')
                ->setInvalidValue($value)
                ->addViolation();
        }
    }
}
