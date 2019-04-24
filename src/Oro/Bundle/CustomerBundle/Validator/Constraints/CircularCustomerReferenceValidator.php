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

        if (in_array(
            $parentCustomer->getId(),
            $this->ownerTreeProvider->getTree()->getSubordinateBusinessUnitIds($value->getId()),
            true
        )) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ parentName }}', $parentCustomer->getName())
                ->setParameter('{{ customerName }}', $value->getName())
                ->addViolation();
        }
    }
}
