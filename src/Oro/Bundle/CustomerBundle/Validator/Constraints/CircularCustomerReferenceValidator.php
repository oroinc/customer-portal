<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Owner\CustomerAwareOwnerTreeInterface;
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

    public function __construct(OwnerTreeProviderInterface $ownerTreeProvider)
    {
        $this->ownerTreeProvider = $ownerTreeProvider;
    }

    /**
     * {@inheritdoc}
     * @param CircularCustomerReference $constraint
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var Customer $value */
        $parentCustomer = $value->getParent();

        if (null === $parentCustomer || null === $parentCustomer->getId()) {
            return;
        }

        if ($value === $parentCustomer) {
            $this->context->buildViolation($constraint->messageItself)
                ->setParameter('{{ customerName }}', $value->getName())
                ->addViolation();

            return;
        }

        if ($this->isAncestor($value, $parentCustomer)) {
            $this->context->buildViolation($constraint->messageCircular)
                ->atPath('parent')
                ->setParameter('{{ parentName }}', $parentCustomer->getName())
                ->setParameter('{{ customerName }}', $value->getName())
                ->addViolation();

            return;
        }

        $children = $value->getChildren();
        if ($children instanceof AbstractLazyCollection && !$children->isInitialized()) {
            return;
        }

        foreach ($children as $child) {
            if ($child->getId() === $parentCustomer->getId()
                || ($value->getId() && $this->isAncestor($child, $value))) {
                $this->context->buildViolation($constraint->messageCircularChild)
                    ->atPath('children')
                    ->setParameter('{{ childName }}', $child->getName())
                    ->setParameter('{{ customerName }}', $value->getName())
                    ->addViolation();
            }
        }
    }

    /**
     * @param Customer $customer
     * @param Customer|null $parent
     * @return bool
     */
    protected function isAncestor(Customer $customer, Customer $parent = null)
    {
        if ($this->ownerTreeProvider instanceof CustomerAwareOwnerTreeInterface) {
            $tree = $this->ownerTreeProvider->getTreeByBusinessUnit($parent);
        } else {
            $tree = $this->ownerTreeProvider->getTree();
        }
        if (in_array(
            $parent->getId(),
            $tree->getSubordinateBusinessUnitIds($customer->getId()),
            true
        )) {
            return true;
        }
        return false;
    }
}
