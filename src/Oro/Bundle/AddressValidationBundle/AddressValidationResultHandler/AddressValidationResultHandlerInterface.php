<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\AddressValidationResultHandler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for the address validation result handler.
 */
interface AddressValidationResultHandlerInterface
{
    public function handleAddressValidationRequest(FormInterface $addressValidationResultForm, Request $request): void;
}
