<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator that checks that parent for the customer is not his child.
 */
class CircularCustomerReferenceValidator extends ConstraintValidator
{
    /** @var OwnerTreeProviderInterface */
    private $ownerTreeProvider;

    /**
     * @param OwnerTreeProviderInterface $ownerTreeProvider
     */
    public function __construct(OwnerTreeProviderInterface $ownerTreeProvider)
    {
        $this->ownerTreeProvider = $ownerTreeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var Customer $value */
        $parentCustomer = $value->getParent();

        if (null === $parentCustomer) {
            return;
        }

        if ($this->isAncestor($value, $parentCustomer)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ parentName }}', $parentCustomer->getName())
                ->setParameter('{{ customerName }}', $value->getName())
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
        if (in_array(
            $parent->getId(),
            $this->ownerTreeProvider->getTree()->getSubordinateBusinessUnitIds($customer->getId()),
            true
        )) {
            return true;
        }

        return false;
    }
}
