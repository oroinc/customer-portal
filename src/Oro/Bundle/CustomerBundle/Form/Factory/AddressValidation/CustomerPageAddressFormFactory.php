<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Form\Factory\AddressValidation;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Form\Factory\AddressValidationAddressFormFactoryInterface;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Creates an address form used for the address validation on the customer create/edit page.
 */
class CustomerPageAddressFormFactory implements AddressValidationAddressFormFactoryInterface
{
    public function __construct(private FormFactoryInterface $formFactory)
    {
    }

    #[\Override]
    public function createAddressForm(Request $request, AbstractAddress $address = null): FormInterface
    {
        $form = $this->formFactory->create(CustomerType::class);
        $addressForm = $form->get('addresses');

        $submittedData = &$request->request->all()[$form->getName()];
        $entryIndex = '0';
        if (!empty($submittedData['addresses']) && is_array($submittedData['addresses'])) {
            $entryIndex = key($submittedData['addresses']);
        }

        $addressForm->setData([$entryIndex => $address]);

        return $addressForm->get((string)$entryIndex);
    }
}
