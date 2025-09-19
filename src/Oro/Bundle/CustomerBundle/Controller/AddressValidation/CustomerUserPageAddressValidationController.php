<?php

namespace Oro\Bundle\CustomerBundle\Controller\AddressValidation;

use Oro\Bundle\AddressValidationBundle\Controller\AbstractAddressValidationController;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Renders the Address Validation dialog, handles its submit for customer user create/edit page.
 */
class CustomerUserPageAddressValidationController extends AbstractAddressValidationController
{
    private const string ACL_CREATE = 'oro_customer_customer_user_create';
    private const string ACL_UPDATE = 'oro_customer_customer_user_update';

    #[CsrfProtection]
    #[Route(
        path: '/{customer_user_id<\d+>}',
        name: 'oro_customer_address_validation_customer_user',
        methods: ['POST']
    )]
    #[Template('@OroAddressValidation/AddressValidation/addressValidationDialogWidget.html.twig')]
    #[\Override]
    public function addressValidationAction(
        Request $request,
        #[MapEntity(id: 'customer_user_id')]
        CustomerUser|null $customerUser = null
    ): Response|array {
        $this->denyAccessUnlessGranted(
            $request->attributes->get('customerUser') ? self::ACL_UPDATE : self::ACL_CREATE
        );

        return parent::addressValidationAction($request);
    }
}
