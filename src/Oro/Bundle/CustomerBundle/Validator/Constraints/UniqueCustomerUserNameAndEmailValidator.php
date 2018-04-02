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
    public function validate($entity, Constraint $constraint)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $entity;

        /** @var CustomerUser $existingCustomerUser */
        $existingCustomerUser = $this->customerUserRepository->findOneBy(
            [
                'email' => $customerUser->getEmail(),
                'isGuest' => false
            ]
        );

        if (!$entity->isGuest() && $existingCustomerUser && $entity->getId() !== $existingCustomerUser->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('email')
                ->setInvalidValue('email')
                ->addViolation();
            return;
        }
    }
}
