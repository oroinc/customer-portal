<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\AddressValidation;

use Oro\Bundle\AddressValidationBundle\Controller\Frontend\AbstractAddressValidationController;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Renders the Address Validation dialog, handles its submit for customer address page on storefront.
 */
class CustomerAddressPageAddressValidationController extends AbstractAddressValidationController
{
    private const string ACL_CREATE = 'oro_customer_frontend_customer_address_create';
    private const string ACL_UPDATE = 'oro_customer_frontend_customer_address_update';

    #[CsrfProtection]
    #[Layout]
    #[ParamConverter('customer', class: Customer::class, options: ['id' => 'customer_id'])]
    #[ParamConverter('customerAddress', class: CustomerAddress::class, options: ['id' => 'id'], isOptional: true)]
    #[Route(
        path: '/{customer_id<\d+>}/{id<\d+>}',
        name: 'oro_customer_frontend_address_validation_customer_address',
        methods: ['POST']
    )]
    #[\Override]
    public function addressValidationAction(Request $request): Response|array
    {
        $this->denyAccessUnlessGranted(
            $request->attributes->get('customerAddress') ? self::ACL_UPDATE : self::ACL_CREATE
        );

        return parent::addressValidationAction($request);
    }
}
