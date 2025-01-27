<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Form\Factory;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for factory the that creates an address form for address validation.
 */
interface AddressValidationAddressFormFactoryInterface
{
    public function createAddressForm(Request $request, AbstractAddress $address = null): FormInterface;
}
