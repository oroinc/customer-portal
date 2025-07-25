<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Controller\AbstractAjaxCustomerUserController;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmail;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * AJAX action for Customer User.
 */
class AjaxCustomerUserController extends AbstractAjaxCustomerUserController
{
    #[Route(
        path: '/get-customer/{id}',
        name: 'oro_customer_frontend_customer_user_get_customer',
        requirements: ['id' => '\d+']
    )]
    #[AclAncestor('oro_customer_frontend_customer_user_view')]
    #[\Override]
    public function getCustomerIdAction(CustomerUser $customerUser)
    {
        return parent::getCustomerIdAction($customerUser);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(path: '/validate', name: 'oro_customer_frontend_customer_user_validate', methods: ['POST'])]
    public function checkEmailAction(Request $request)
    {
        $value = $request->get('value');
        $validator = $this->container->get(ValidatorInterface::class);
        $violations = $validator->validate($value, [new UniqueCustomerUserNameAndEmail()]);

        return new JsonResponse(['valid' => count($violations) === 0]);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ValidatorInterface::class,
            ]
        );
    }
}
