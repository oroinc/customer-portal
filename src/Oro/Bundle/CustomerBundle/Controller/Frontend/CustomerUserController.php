<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD controller for CustomerUser entity
 */
class CustomerUserController extends AbstractController
{
    /**
     * @Route("/view/{id}", name="oro_customer_frontend_customer_user_view", requirements={"id"="\d+"})
     * @Layout
     * @Acl(
     *      id="oro_customer_frontend_customer_user_view",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUser",
     *      permission="VIEW",
     *      group_name="commerce"
     * )
     *
     * @param CustomerUser $customerUser
     * @return array
     */
    public function viewAction(CustomerUser $customerUser)
    {
        return [
            'data' => [
                'entity' => $customerUser
            ]
        ];
    }

    /**
     * @Route("/", name="oro_customer_frontend_customer_user_index")
     * @Layout(vars={"entity_class"})
     * @AclAncestor("oro_customer_frontend_customer_user_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => CustomerUser::class
        ];
    }

    /**
     * Create customer user form
     *
     * @Route("/create", name="oro_customer_frontend_customer_user_create")
     * @Layout
     * @Acl(
     *      id="oro_customer_frontend_customer_user_create",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUser",
     *      permission="CREATE",
     *      group_name="commerce"
     * )
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        return $this->update(new CustomerUser(), $request);
    }

    /**
     * Edit customer user form
     *
     * @Route("/update/{id}", name="oro_customer_frontend_customer_user_update", requirements={"id"="\d+"})
     * @Layout
     * @Acl(
     *      id="oro_customer_frontend_customer_user_update",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUser",
     *      permission="EDIT",
     *      group_name="commerce"
     * )
     * @param CustomerUser $customerUser
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function updateAction(CustomerUser $customerUser, Request $request)
    {
        return  $this->update($customerUser, $request);
    }

    /**
     * @param CustomerUser $customerUser
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function update(CustomerUser $customerUser, Request $request)
    {
        $form = $this->get('oro_customer.provider.frontend_customer_user_form')
            ->getCustomerUserForm($customerUser);
        $handler = new CustomerUserHandler(
            $form,
            $request,
            $this->get('oro_customer_user.manager'),
            $this->get('oro_security.token_accessor'),
            $this->get('translator'),
            $this->get('logger')
        );

        $result = $this->get('oro_form.model.update_handler')->update(
            $customerUser,
            $form,
            $this->get('translator')->trans('oro.customer.controller.customeruser.saved.message'),
            $handler
        );

        if ($result instanceof Response) {
            return $result;
        }

        return [
            'data' => [
                'entity' => $customerUser
            ]
        ];
    }
}
