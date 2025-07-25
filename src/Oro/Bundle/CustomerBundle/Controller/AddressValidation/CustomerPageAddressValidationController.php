<?php

namespace Oro\Bundle\CustomerBundle\Controller\AddressValidation;

use Oro\Bundle\AddressValidationBundle\Controller\AbstractAddressValidationController;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Renders the Address Validation dialog, handles its submit for customer create/edit page.
 */
class CustomerPageAddressValidationController extends AbstractAddressValidationController
{
    private const string ACL_ADDRESS_CREATE = 'oro_customer_create';
    private const string ACL_ADDRESS_UPDATE = 'oro_customer_customer_update';

    #[CsrfProtection]
    #[ParamConverter('customer', class: Customer::class, options: ['id' => 'customer_id'], isOptional: true)]
    #[Route(
        path: '/{customer_id<\d+>}',
        name: 'oro_customer_address_validation_customer',
        methods: ['POST']
    )]
    #[Template('@OroAddressValidation/AddressValidation/addressValidationDialogWidget.html.twig')]
    #[\Override]
    public function addressValidationAction(Request $request): Response|array
    {
        $this->denyAccessUnlessGranted(
            $request->attributes->get('customer') ? self::ACL_ADDRESS_UPDATE : self::ACL_ADDRESS_CREATE
        );

        return parent::addressValidationAction($request);
    }
}
