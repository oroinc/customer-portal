<?php

namespace Oro\Bundle\CustomerBundle\Controller\AddressValidation;

use Oro\Bundle\AddressValidationBundle\Controller\AbstractAddressValidationController;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Renders the Address Validation dialog, handles its submit for customer user address dialog.
 */
class CustomerUserAddressDialogAddressValidationController extends AbstractAddressValidationController
{
    private const string ACL_CREATE = 'oro_customer_customer_user_address_create';
    private const string ACL_UPDATE = 'oro_customer_customer_user_address_update';

    #[CsrfProtection]
    #[ParamConverter('customer_user', class: CustomerUser::class, options: ['id' => 'customer_user_id'])]
    #[ParamConverter(
        data: 'customerUserAddress',
        class: CustomerUserAddress::class,
        options: ['id' => 'id'],
        isOptional: true
    )]
    #[Route(
        path: '/{customer_user_id<\d+>}/{id<\d+>}',
        name: 'oro_customer_address_validation_customer_user_address',
        methods: ['POST']
    )]
    #[Template('@OroAddressValidation/AddressValidation/addressValidationDialogWidget.html.twig')]
    #[\Override]
    public function addressValidationAction(Request $request): Response|array
    {
        $this->denyAccessUnlessGranted(
            $request->attributes->get('customerUserAddress') ? self::ACL_UPDATE : self::ACL_CREATE
        );

        return parent::addressValidationAction($request);
    }
}
