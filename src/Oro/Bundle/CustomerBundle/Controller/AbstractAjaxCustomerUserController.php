<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides common AJAX actions for customer user controllers.
 *
 * This base class implements reusable AJAX endpoints for retrieving customer user-related data in JSON format.
 * Subclasses can extend this to add additional AJAX actions while inheriting the common customer user operations.
 */
abstract class AbstractAjaxCustomerUserController extends AbstractController
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
