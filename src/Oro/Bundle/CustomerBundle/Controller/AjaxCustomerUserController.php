<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AJAX action for Customer User.
 */
class AjaxCustomerUserController extends AbstractAjaxCustomerUserController
{
    #[Route(
        path: '/get-customer/{id}',
        name: 'oro_customer_customer_user_get_customer',
        requirements: ['id' => '\d+']
    )]
    #[AclAncestor('oro_customer_customer_user_view')]
    #[\Override]
    public function getCustomerIdAction(CustomerUser $customerUser)
    {
        return parent::getCustomerIdAction($customerUser);
    }
}
