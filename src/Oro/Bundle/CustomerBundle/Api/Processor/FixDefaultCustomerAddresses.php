<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\CustomerBundle\Form\EventListener\FixCustomerAddressesDefaultSubscriber;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Ensures that there is only one default address per type.
 */
class FixDefaultCustomerAddresses implements ProcessorInterface
{
    /**
     * The property path to collection of all addresses
     * (e.g. "owner.addresses" means $address->getOwner()->getAddresses())
     */
    private string $addressesPropertyPath;
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(string $addressesPropertyPath, PropertyAccessorInterface $propertyAccessor)
    {
        $this->addressesPropertyPath = $addressesPropertyPath;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeFormDataContext $context */

        $handler = new FixCustomerAddressesDefaultSubscriber($this->addressesPropertyPath, $this->propertyAccessor);
        $handler->postSubmit(new FormEvent($context->getForm(), $context->getData()));
    }
}
