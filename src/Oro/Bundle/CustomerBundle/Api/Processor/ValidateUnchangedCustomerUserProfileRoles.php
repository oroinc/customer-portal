<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Form\FormUtil;
use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\CustomerBundle\Api\CustomerUserProfileResolver;
use Oro\Bundle\FormBundle\Validator\Constraints\UnchangeableField;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Responsible for the validation of the 'roles' field. Сannot change roles in own profile.
 */
class ValidateUnchangedCustomerUserProfileRoles implements ProcessorInterface
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /**
     * @var CustomerUserProfileResolver
     */
    private $customerUserProfileResolver;

    /**
     * @param CustomerUserProfileResolver $customerUserProfileResolver
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        CustomerUserProfileResolver $customerUserProfileResolver,
        DoctrineHelper $doctrineHelper
    ) {
        $this->customerUserProfileResolver = $customerUserProfileResolver;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeFormDataContext $context */
        $form = $context->findFormField('roles');
        if (null === $form) {
            return;
        }

        $customerUser = $context->getResult();
        if (!$this->customerUserProfileResolver->hasProfilePermission($context, $customerUser->getId())) {
            return;
        }

        $entityManager = $this->doctrineHelper->getEntityManager($customerUser);
        // Unable to retrieve a collection of roles from the customer user entity, take the roles from the source data.
        $originalCustomerUser = $entityManager->getUnitOfWork()->getOriginalEntityData($customerUser);
        // If the roles are different from the original, return validation error, because it is impossible to change
        // the role of the customer user from the profile.
        // No need to check the entity relation, any roles changes for the profile are prohibited.
        $isRolesUpdated = $originalCustomerUser['roles']->isDirty();
        if ($isRolesUpdated) {
            FormUtil::addFormConstraintViolation($form, new UnchangeableField());
        }
    }
}
