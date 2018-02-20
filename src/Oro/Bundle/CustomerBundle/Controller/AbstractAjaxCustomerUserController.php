<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractAjaxCustomerUserController extends Controller
{
    /**
     * @param CustomerUser $customerUser
     * @return JsonResponse
     */
    public function getCustomerIdAction(CustomerUser $customerUser)
    {
        return new JsonResponse([
            'customerId' => $customerUser->getCustomer() ? $customerUser->getCustomer()->getId() : null
        ]);
    }
}
