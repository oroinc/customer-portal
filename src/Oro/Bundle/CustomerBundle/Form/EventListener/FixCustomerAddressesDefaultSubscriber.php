<?php

namespace Oro\Bundle\CustomerBundle\Form\EventListener;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Ensures that there is only one default address per type.
 */
class FixCustomerAddressesDefaultSubscriber implements EventSubscriberInterface
{
    /**
     * The property path to collection of all addresses
     * (e.g. "owner.addresses" means $address->getOwner()->getAddresses())
     *
     * @var string
     */
    private $addressesPropertyPath;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    public function __construct(string $addressesPropertyPath, PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->addressesPropertyPath = $addressesPropertyPath;
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => 'postSubmit'
        ];
    }

    public function postSubmit(FormEvent $event)
    {
        /** @var AbstractDefaultTypedAddress $address */
        $address = $event->getData();
        $allAddresses = $this->getAllAddresses($address);
        if (null === $allAddresses) {
            return;
        }

        $addressDefaults = $address->getDefaults();
        foreach ($allAddresses as $otherAddress) {
            if ($address === $otherAddress) {
                continue;
            }

            $otherAddressDefaults = $otherAddress->getDefaults();
            foreach ($addressDefaults as $addressDefaultType) {
                foreach ($otherAddressDefaults as $otherAddressDefault) {
                    if ($otherAddressDefault->getName() === $addressDefaultType->getName()
                        && $otherAddressDefaults->contains($otherAddressDefault)
                    ) {
                        $otherAddressDefaults->removeElement($otherAddressDefault);
                    }
                }
            }
            $otherAddress->setDefaults($otherAddressDefaults);
        }
    }

    /**
     * @param AbstractDefaultTypedAddress $address
     *
     * @return AbstractDefaultTypedAddress[]|Collection|null
     */
    private function getAllAddresses(AbstractDefaultTypedAddress $address)
    {
        $path = explode('.', $this->addressesPropertyPath);
        $addressesField = array_pop($path);
        if (count($path) === 0) {
            throw new InvalidPropertyPathException(sprintf(
                'The addresses property path "%s" must have at least 2 elements.',
                $this->addressesPropertyPath
            ));
        }
        $addressesOwner = $address;
        foreach ($path as $fieldName) {
            $addressesOwner = $this->propertyAccessor->getValue($addressesOwner, $fieldName);
            if (null === $addressesOwner) {
                break;
            }
        }
        if (null === $addressesOwner) {
            return null;
        }

        return $this->propertyAccessor->getValue($addressesOwner, $addressesField);
    }
}
