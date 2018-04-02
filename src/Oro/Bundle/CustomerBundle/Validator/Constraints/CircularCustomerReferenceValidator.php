<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CircularCustomerReferenceValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     * @param Customer $value
     */
    public function validate($value, Constraint $constraint)
    {
        $customer = $this->context->getObject();
        if ($this->isAncestor($customer, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ parentName }}', $value->getName())
                ->setParameter('{{ customerName }}', $customer->getName())
                ->addViolation();
        }
    }

    /**
     * @param Customer $customer
     * @param Customer|null $parent
     * @return bool
     */
    protected function isAncestor(Customer $customer, Customer $parent = null)
    {
        if ($parent && ($ancestor = $parent->getParent())) {
            if ($customer->getId() !== $ancestor->getId()) {
                return $this->isAncestor($customer, $ancestor);
            }

            return true;
        }

        return false;
    }
}
