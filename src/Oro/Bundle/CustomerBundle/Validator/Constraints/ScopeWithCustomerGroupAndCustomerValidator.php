<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\ScopeBundle\Entity\Scope;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks whether the scopes with customer and customer group are used.
 */
class ScopeWithCustomerGroupAndCustomerValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof Scope) {
            return;
        }

        if ($value->getCustomer() && $value->getCustomerGroup()) {
            $this->context->addViolation($constraint->message);
        }
    }
}
