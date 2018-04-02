<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueCustomerUserNameAndEmailValidator extends ConstraintValidator
{
    /**
     * @var  EntityRepository
     */
    private $customerUserRepository;

    /**
     * @param EntityRepository $customerUserRepository
     */
    public function __construct(EntityRepository $customerUserRepository)
    {
        $this->customerUserRepository = $customerUserRepository;
    }

    /**
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

        /** @var CustomerUser $existingCustomerUser */
        $existingCustomerUser = $this->customerUserRepository->findOneBy(
            [
                'email' => $value,
                'isGuest' => false
            ]
        );

        if ($existingCustomerUser && $id !== $existingCustomerUser->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('email')
                ->setInvalidValue('email')
                ->addViolation();
            return;
        }
    }
}
