<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator which checks that CustomerUser entity has unique email.
 */
class UniqueCustomerUserNameAndEmailValidator extends ConstraintValidator
{
    /**
     * @var CustomerUserManager
     */
    private $customerUserManager;

    /**
     * @param CustomerUserManager $customerUserManager
     */
    public function __construct(CustomerUserManager $customerUserManager)
    {
        $this->customerUserManager = $customerUserManager;
    }

    /**
     * @param CustomerUser $entity
     * @param UniqueCustomerUserNameAndEmail $constraint
     *
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($entity->isGuest()) {
            return;
        }

        /** @var CustomerUser $existingCustomerUser */
        $existingCustomerUser = $this->customerUserManager->findUserByEmail((string)$entity->getEmail());

        if ($existingCustomerUser && $entity->getId() !== $existingCustomerUser->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('email')
                ->setInvalidValue($entity->getEmail())
                ->addViolation();

            return;
        }
    }
}
