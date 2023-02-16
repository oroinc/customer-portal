<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Form\FormUtil;
use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\CustomerBundle\Api\CustomerUserProfileResolver;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FormBundle\Validator\Constraints\UnchangeableField;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Validates that roles cannot be changed in own profile.
 */
class ValidateUnchangedCustomerUserProfileRoles implements ProcessorInterface
{
    private DoctrineHelper $doctrineHelper;
    private CustomerUserProfileResolver $customerUserProfileResolver;

    public function __construct(
        CustomerUserProfileResolver $customerUserProfileResolver,
        DoctrineHelper $doctrineHelper
    ) {
        $this->customerUserProfileResolver = $customerUserProfileResolver;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeFormDataContext $context */

        $form = $context->findFormField('userRoles');
        if (null === $form) {
            return;
        }

        /** @var CustomerUser $customerUser */
        $customerUser = $context->getResult();
        if (!$this->customerUserProfileResolver->hasProfilePermission($context, $customerUser->getId())) {
            return;
        }

        // Unable to retrieve a collection of roles from the customer user entity, take the roles from the source data.
        $originalCustomerUser = $this->doctrineHelper->getEntityManager($customerUser)
            ->getUnitOfWork()
            ->getOriginalEntityData($customerUser);
        // If the roles are different from the original, return validation error, because it is impossible to change
        // the role of the customer user from the profile.
        // No need to check the entity relation, any roles changes for the profile are prohibited.
        $isRolesUpdated = $originalCustomerUser['userRoles']->isDirty();
        if ($isRolesUpdated) {
            FormUtil::addFormConstraintViolation($form, new UnchangeableField());
        }
    }
}
