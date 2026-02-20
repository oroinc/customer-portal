<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
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
        private AuthorizationCheckerInterface $authorizationChecker,
        private FrontendOwnershipMetadataProvider $frontendOwnershipMetadataProvider,
        private PropertyAccessorInterface $propertyAccessor
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomerOwner) {
            throw new UnexpectedTypeException($constraint, CustomerOwner::class);
        }

        if (null === $value) {
            return;
        }

        if ($value instanceof CustomerOwnerAwareInterface) {
            $customer = $value->getCustomer();
            if (null === $customer) {
                return;
            }
            $customerUser = $value->getCustomerUser();
            $this->validateOwnership($customer, $customerUser, $constraint);

            return;
        }

        $metadata = $this->getFrontendOwnershipMetadata(ClassUtils::getClass($value));
        if ($metadata === null) {
            return;
        }

        $customer = $this->propertyAccessor->getValue($value, $metadata->getCustomerFieldName());
        $customerUser = $this->propertyAccessor->getValue($value, $metadata->getOwnerFieldName());
        $this->validateOwnership($customer, $customerUser, $constraint);
    }

    private function validateOwnership(
        ?Customer $customer,
        ?CustomerUser $customerUser,
        CustomerOwner $constraint
    ): void {
        if (null === $customer || null === $customerUser) {
            return;
        }

        $customerUserCustomer = $customerUser->getCustomer();
        if (null === $customerUserCustomer) {
            return;
        }

        if ($customerUserCustomer !== $customer
            && $this->authorizationChecker->isGranted(BasicPermission::VIEW, $customer)
            && $this->authorizationChecker->isGranted(BasicPermission::VIEW, $customerUser)
        ) {
            $this->context->addViolation($constraint->message);
        }
    }

    private function getFrontendOwnershipMetadata(string $entityClass): ?FrontendOwnershipMetadata
    {
        $metadata = $this->frontendOwnershipMetadataProvider->getMetadata($entityClass);

        if ($metadata instanceof FrontendOwnershipMetadata
            && $metadata->hasOwner()
            && $metadata->isUserOwned()
            && $metadata->getCustomerFieldName()
        ) {
            return $metadata;
        }

        return null;
    }
}
