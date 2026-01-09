<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that a customer user assigned to an entity
 * belongs to a customer assigned to this entity.
 */
class CustomerOwnerValidator extends ConstraintValidator
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[\Override]
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomerOwner) {
            throw new UnexpectedTypeException($constraint, CustomerOwner::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof CustomerOwnerAwareInterface) {
            throw new UnexpectedTypeException($value, CustomerOwnerAwareInterface::class);
        }

        $customer = $value->getCustomer();
        if (null === $customer) {
            return;
        }

        $customerUser = $value->getCustomerUser();
        if (null === $customerUser) {
            return;
        }

        $customerUserCustomer = $customerUser->getCustomer();
        if (null === $customerUserCustomer) {
            return;
        }

        if (
            $customerUserCustomer !== $customer
            && $this->authorizationChecker->isGranted(BasicPermission::VIEW, $customer)
            && $this->authorizationChecker->isGranted(BasicPermission::VIEW, $customerUser)
        ) {
            $this->context->addViolation($constraint->message);
        }
    }
}
