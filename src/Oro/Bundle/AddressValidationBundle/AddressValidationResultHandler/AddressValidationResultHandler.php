<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\AddressValidationResultHandler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles the address validation result form.
 */
class AddressValidationResultHandler implements AddressValidationResultHandlerInterface
{
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

        $addressValidationResult['address']->setValidatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
    }
}
