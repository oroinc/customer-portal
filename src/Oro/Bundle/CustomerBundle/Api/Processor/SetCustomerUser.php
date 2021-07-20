<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Assigns an entity to the current customer user.
 */
class SetCustomerUser implements ProcessorInterface
{
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    /** @var string */
    private $customerUserFieldName;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        TokenAccessorInterface $tokenAccessor,
        string $customerUserFieldName = 'customerUser'
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->tokenAccessor = $tokenAccessor;
        $this->customerUserFieldName = $customerUserFieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var CustomizeFormDataContext $context */

        $customerUserFormField = $context->findFormField($this->customerUserFieldName);
        if (null === $customerUserFormField
            || !$customerUserFormField->isSubmitted()
            || !$customerUserFormField->getConfig()->getMapped()
        ) {
            $this->setCustomerUser($context->getData());
        }
    }

    /**
     * Returns a customer user a processing entity should be assigned to.
     */
    private function getCustomerUser(): ?CustomerUser
    {
        $user = $this->tokenAccessor->getUser();
        if (!$user instanceof CustomerUser) {
            return null;
        }

        return $user;
    }

    /**
     * Assigns the given entity to a customer user returned by getCustomerUser() method.
     * The entity's customer user property will not be changed if the getCustomerUser() method returns NULL
     * or the entity is already assigned to a customer user.
     *
     * @param object $entity
     */
    private function setCustomerUser($entity): void
    {
        $entityCustomerUser = $this->propertyAccessor->getValue($entity, $this->customerUserFieldName);
        if (null === $entityCustomerUser) {
            $customerUser = $this->getCustomerUser();
            if (null !== $customerUser) {
                $this->propertyAccessor->setValue($entity, $this->customerUserFieldName, $customerUser);
            }
        }
    }
}
