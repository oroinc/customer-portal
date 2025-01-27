<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Form\Factory\AddressValidation;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Form\Factory\AddressValidationAddressFormFactoryInterface;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerTypedAddressType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Creates an address form used for the address validation in the customer address create/edit dialog.
 */
class CustomerAddressDialogAddressFormFactory implements AddressValidationAddressFormFactoryInterface
{
    public function __construct(private FormFactoryInterface $formFactory)
    {
    }

    #[\Override]
    public function createAddressForm(Request $request, AbstractAddress $address = null): FormInterface
    {
        if ($address === null) {
            $address = (new CustomerAddress())
                ->setFrontendOwner(new Customer());
        }

        return $this->formFactory->create(CustomerTypedAddressType::class, $address);
    }
}
