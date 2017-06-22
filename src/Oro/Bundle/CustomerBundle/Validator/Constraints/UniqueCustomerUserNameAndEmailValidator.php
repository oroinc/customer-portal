<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

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
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $entity;

        /** @var CustomerUser[] $existingCustomerUsers */
        $existingCustomerUsers = $this->customerUserRepository->findBy(['email' => $customerUser->getEmail()]);
        if (!$existingCustomerUsers) {
            return;
        }

        foreach ($existingCustomerUsers as $customerUser) {
            if (!$customerUser->isGuest()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('email')
                    ->setInvalidValue('email')
                    ->addViolation();
                return;
            }
        }
    }
}
