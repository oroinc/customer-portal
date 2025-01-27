<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Form\Factory\AddressValidation\Frontend;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Form\Factory\AddressValidationAddressFormFactoryInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserTypedAddressType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Creates an address form used for the address validation on the customer user address create/edit page
 * on storefront.
 */
class CustomerUserAddressPageAddressFormFactory implements AddressValidationAddressFormFactoryInterface
{
    public function __construct(private FormFactoryInterface $formFactory)
    {
    }

    #[\Override]
    public function createAddressForm(Request $request, AbstractAddress $address = null): FormInterface
    {
        if ($address === null) {
            $address = (new CustomerUserAddress())
                ->setFrontendOwner(new CustomerUser());
        }

        return $this->formFactory->create(FrontendCustomerUserTypedAddressType::class, $address);
    }
}
