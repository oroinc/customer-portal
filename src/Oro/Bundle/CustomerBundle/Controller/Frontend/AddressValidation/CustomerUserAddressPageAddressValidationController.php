<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\AddressValidation;

use Oro\Bundle\AddressValidationBundle\Controller\Frontend\AbstractAddressValidationController;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Renders the Address Validation dialog, handles its submit for customer user address page on storefront.
 */
class CustomerUserAddressPageAddressValidationController extends AbstractAddressValidationController
{
    private const string ACL_CREATE = 'oro_customer_frontend_customer_user_address_create';
    private const string ACL_UPDATE = 'oro_customer_frontend_customer_user_address_update';

    #[CsrfProtection]
    #[Layout]
    #[Route(
        path: '/{customer_user_id<\d+>}/{id<\d+>}',
        name: 'oro_customer_frontend_address_validation_customer_user_address',
        methods: ['POST']
    )]
    #[\Override]
    public function addressValidationAction(
        Request $request,
        #[MapEntity(id: 'customer_user_id')]
        CustomerUser|null $customerUser = null,
        #[MapEntity(id: 'id')]
        CustomerUserAddress|null $customerUserAddress = null
    ): Response|array {
        $this->denyAccessUnlessGranted(
            $request->attributes->get('customerUserAddress') ? self::ACL_UPDATE : self::ACL_CREATE
        );

        return parent::addressValidationAction($request);
    }
}
