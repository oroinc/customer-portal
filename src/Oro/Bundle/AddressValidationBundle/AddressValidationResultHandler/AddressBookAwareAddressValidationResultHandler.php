<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\AddressValidationResultHandler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressValidationBundle\Model\AddressValidatedAtAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\AddressBookAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Utils\AddressBookAddressUtils;
use Oro\Bundle\CustomerBundle\Utils\AddressCopier;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Handles the address validation result form.
 * Creates an address in an address book or updates the related one.
 */
class AddressBookAwareAddressValidationResultHandler implements AddressValidationResultHandlerInterface
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private AuthorizationCheckerInterface $authorizationChecker,
        private AddressCopier $addressCopier,
        private string $addressType
    ) {
    }

    public function handleAddressValidationRequest(FormInterface $addressValidationResultForm, Request $request): void
    {
        $formConfig = $addressValidationResultForm->getConfig();
        $suggestedAddresses = $formConfig->getOption('suggested_addresses');

        if (!count($suggestedAddresses)) {
            $addressValidationResultForm->submit(['address' => '0']);
        } else {
            $addressValidationResultForm->handleRequest($request);
        }

        if (!$addressValidationResultForm->isSubmitted() || !$addressValidationResultForm->isValid()) {
            return;
        }

        $addressValidationResult = $addressValidationResultForm->getData();
        $originalAddress = $formConfig->getOption('original_address');
        $updateAddress = $addressValidationResult['update_address'] ?? false;
        $createAddress = $addressValidationResult['create_address'] ?? false;

        if ($createAddress === false) {
            if ($originalAddress instanceof AddressBookAwareInterface) {
                $addressBookAddress = AddressBookAddressUtils::getAddressBookAddress($originalAddress);
            } else {
                $addressBookAddress = null;
            }
        } else {
            $addressBookAddress = $this->createAddressBookAddress($formConfig);
        }

        $this->handleSelectedAddress(
            $addressValidationResult['address'],
            $originalAddress,
            $addressBookAddress,
            $updateAddress || $createAddress
        );
    }

    private function createAddressBookAddress(FormConfigInterface $formConfig): CustomerAddress|CustomerUserAddress|null
    {
        $addressBookNewAddressClass = $formConfig->getOption('address_book_new_address_class');
        if ($addressBookNewAddressClass === null) {
            return null;
        }

        $customer = $formConfig->getOption('customer');
        $customerUser = $formConfig->getOption('customer_user');

        $addressBookAddress = new $addressBookNewAddressClass();
        AddressBookAddressUtils::setFrontendOwner($addressBookAddress, $customerUser ?? $customer);
        $addressBookAddress->addType($this->getAddressType());

        return $addressBookAddress;
    }

    private function getAddressType(): AddressType
    {
        return $this->doctrine
            ->getManagerForClass(AddressType::class)
            ->getReference(AddressType::class, $this->addressType);
    }

    private function handleSelectedAddress(
        AbstractAddress&AddressValidatedAtAwareInterface $selectedAddress,
        AbstractAddress&AddressValidatedAtAwareInterface $originalAddress,
        CustomerAddress|CustomerUserAddress|null $addressBookAddress,
        bool $saveAddressBookAddress
    ): void {
        $selectedAddress->setValidatedAt(new \DateTime('now', new \DateTimeZone('UTC')));

        $doFlush = false;
        if ($addressBookAddress !== null) {
            if ($saveAddressBookAddress === true) {
                if ($selectedAddress instanceof AddressBookAwareInterface) {
                    AddressBookAddressUtils::setAddressBookAddress($selectedAddress, $addressBookAddress);
                }

                $this->addressCopier->copyToAddress($selectedAddress, $addressBookAddress);

                $doFlush = true;
            } elseif ($selectedAddress === $originalAddress &&
                $this->authorizationChecker->isGranted(BasicPermission::EDIT, $addressBookAddress)) {
                $addressBookAddress->setValidatedAt(clone $selectedAddress->getValidatedAt());

                $doFlush = true;
            } else {
                // Nothing to do.
            }
        } elseif ($selectedAddress instanceof AddressBookAwareInterface) {
            AddressBookAddressUtils::resetAddressBookAddress($selectedAddress);
        } else {
            // Nothing to do.
        }

        if ($doFlush === true) {
            $entityManager = $this->doctrine->getManagerForClass($addressBookAddress::class);
            $entityManager->flush();
        }
    }
}
