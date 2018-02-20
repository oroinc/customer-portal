<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CustomerUserCheckRoleValidator extends ConstraintValidator
{
    /**
     * @param CustomerUserCheckRole $constraint
     * @throws UnexpectedTypeException
     *
     * {@inheritdoc}
     */
    public function validate($customer, Constraint $constraint)
    {
        if (!$customer instanceof CustomerUser) {
            throw new UnexpectedTypeException($customer, CustomerUser::class);
        }

        if (!$customer->isEnabled()) {
            return;
        }

        if (empty($customer->getRoles())) {
            $this->context->addViolation($constraint->message);
        }
    }
}
