<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Controller\AbstractAjaxCustomerUserController;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmail;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * AJAX action for Customer User.
 */
class AjaxCustomerUserController extends AbstractAjaxCustomerUserController
{
    /**
     * @Route("/get-customer/{id}",
     *      name="oro_customer_frontend_customer_user_get_customer",
     *      requirements={"id"="\d+"}
     * )
     * @AclAncestor("oro_customer_frontend_customer_user_view")
     *
     * {@inheritdoc}
     */
    public function getCustomerIdAction(CustomerUser $customerUser)
    {
        return parent::getCustomerIdAction($customerUser);
    }

    /**
     * @Route("/validate", name="oro_customer_frontend_customer_user_validate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function checkEmailAction(Request $request)
    {
        $value = $request->get('value');
        $validator = $this->get(ValidatorInterface::class);
        $violations = $validator->validate($value, [new UniqueCustomerUserNameAndEmail()]);

        return new JsonResponse(['valid' => count($violations) === 0]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ValidatorInterface::class,
            ]
        );
    }
}
