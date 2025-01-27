<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Form\Factory\AddressValidation;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Form\Factory\AddressValidationAddressFormFactoryInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Create an address form used for the address validation on the customer user create/edit page.
 */
class CustomerUserPageAddressFormFactory implements AddressValidationAddressFormFactoryInterface
{
    public function __construct(private FormFactoryInterface $formFactory)
    {
    }

    #[\Override]
    public function createAddressForm(Request $request, AbstractAddress $address = null): FormInterface
    {
        $form = $this->formFactory->create(CustomerUserType::class, new CustomerUser());
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
