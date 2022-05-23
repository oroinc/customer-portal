<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Form\FormUtil;
use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Assigns a system organization aware entity to the current organization.
 */
class SetSystemOrganization implements ProcessorInterface
{
    private PropertyAccessorInterface $propertyAccessor;
    private TokenAccessorInterface $tokenAccessor;
    private OwnershipMetadataProviderInterface $ownershipMetadataProvider;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        TokenAccessorInterface $tokenAccessor,
        OwnershipMetadataProviderInterface $ownershipMetadataProvider
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->tokenAccessor = $tokenAccessor;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeFormDataContext $context */

        $ownershipMetadata = $this->ownershipMetadataProvider->getMetadata($context->getClassName());
        if ($ownershipMetadata->hasOwner()) {
            $entity = $context->getData();
            if ($ownershipMetadata->isUserOwned() || $ownershipMetadata->isBusinessUnitOwned()) {
                $this->setOrganization($entity, $ownershipMetadata->getOrganizationFieldName(), $context);
            } elseif ($ownershipMetadata->isOrganizationOwned()) {
                $this->setOrganization($entity, $ownershipMetadata->getOwnerFieldName(), $context);
            }
        }
    }

    private function setOrganization(
        object $entity,
        ?string $organizationFieldName,
        CustomizeFormDataContext $context
    ): void {
        if (!$organizationFieldName) {
            return;
        }

        $entityOrganization = $this->propertyAccessor->getValue($entity, $organizationFieldName);
        if (null === $entityOrganization) {
            $organization = $this->tokenAccessor->getOrganization();
            if (null !== $organization) {
                $this->propertyAccessor->setValue($entity, $organizationFieldName, $organization);
                FormUtil::removeAccessGrantedValidationConstraint($context->getForm(), $organizationFieldName);
            }
        }
    }
}
