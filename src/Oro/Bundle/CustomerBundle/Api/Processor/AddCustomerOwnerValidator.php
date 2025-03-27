<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\GetConfig\ConfigContext;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\ApiBundle\Util\ValidationHelper;
use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerOwner;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Adds the validation constraint that is used to validate that
 * a customer user assigned to an entity belongs to a customer assigned to this entity.
 */
class AddCustomerOwnerValidator implements ProcessorInterface
{
    public function __construct(
        private DoctrineHelper $doctrineHelper,
        private ValidationHelper $validationHelper
    ) {
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var ConfigContext $context */

        $entityClass = $context->getClassName();
        if (!$this->doctrineHelper->isManageableEntityClass($entityClass)) {
            // only manageable entities are supported
            return;
        }
        if (!is_subclass_of($entityClass, CustomerOwnerAwareInterface::class)) {
            // only entities that implement CustomerOwnerAwareInterface are supported
            return;
        }

        if (!$this->validationHelper->hasValidationConstraintForClass($entityClass, CustomerOwner::class)) {
            $context->getResult()->addFormConstraint(new CustomerOwner(['groups' => ['api']]));
        }
    }
}
