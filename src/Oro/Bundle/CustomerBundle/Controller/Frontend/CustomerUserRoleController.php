<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserRoleUpdateFrontendHandler;
use Oro\Bundle\FormBundle\Model\UpdateHandler;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Storefront CRUD for customer user roles.
 */
class CustomerUserRoleController extends AbstractController
{
    /**
     * @Route("/", name="oro_customer_frontend_customer_user_role_index")
     * @Layout(vars={"entity_class"})
     * @Acl(
     *      id="oro_customer_frontend_customer_user_role_index",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUserRole",
     *      permission="VIEW",
     *      group_name="commerce"
     * )
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => CustomerUserRole::class,
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_customer_frontend_customer_user_role_view", requirements={"id"="\d+"})
     * @Layout()
     *
     * @param CustomerUserRole $role
     * @return array
     */
    public function viewAction(CustomerUserRole $role)
    {
        $isGranted = $role->isPredefined()
            ? $this->isGranted('oro_customer_frontend_customer_user_role_view')
            : $this->isGranted('FRONTEND_CUSTOMER_ROLE_VIEW', $role);

        if (!$isGranted || !$role->isSelfManaged() || !$role->isPublic()) {
            throw $this->createAccessDeniedException();
        }

        return [
            'data' => [
                'entity' => $role
            ]
        ];
    }

    /**
     * @Route("/create", name="oro_customer_frontend_customer_user_role_create")
     * @Layout()
     * @Acl(
     *      id="oro_customer_frontend_customer_user_role_create",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUserRole",
     *      permission="CREATE",
     *      group_name="commerce"
     * )
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update(new CustomerUserRole());
    }

    /**
     * @Route("/update/{id}", name="oro_customer_frontend_customer_user_role_update", requirements={"id"="\d+"})
     * @Layout()
     *
     * @param CustomerUserRole $role
     * @param Request $request
     * @return array
     */
    public function updateAction(CustomerUserRole $role, Request $request)
    {
        $isGranted = $role->isPredefined()
            ? $this->isGranted('oro_customer_frontend_customer_user_role_create')
            : $this->isGranted('FRONTEND_CUSTOMER_ROLE_UPDATE', $role);

        if (!$isGranted || !$role->isSelfManaged() || !$role->isPublic()) {
            throw $this->createAccessDeniedException();
        }

        if ($role->isPredefined() && $request->isMethod(Request::METHOD_GET)) {
            $this->addFlash(
                'warning',
                $this->get(TranslatorInterface::class)
                    ->trans('oro.customer.customeruserrole.frontend.edit-predifined-role.message')
            );
        }

        return $this->update($role);
    }

    /**
     * @param CustomerUserRole $role
     * @return array|RedirectResponse
     */
    protected function update(CustomerUserRole $role)
    {
        $handler = $this->get(CustomerUserRoleUpdateFrontendHandler::class);
        $form = $handler->createForm($role);

        // This is cloned role in case of original role was predefined
        $customizableRole = $form->getData();

        $response = $this->get(UpdateHandler::class)->handleUpdate(
            $customizableRole,
            $form,
            function (CustomerUserRole $role) {
                return [
                    'route' => 'oro_customer_frontend_customer_user_role_update',
                    'parameters' => ['id' => $role->getId()],
                ];
            },
            function (CustomerUserRole $role) {
                return [
                    'route' => 'oro_customer_frontend_customer_user_role_view',
                    'parameters' => ['id' => $role->getId()],
                ];
            },
            $this->get(TranslatorInterface::class)->trans('oro.customer.controller.customeruserrole.saved.message'),
            $handler
        );

        if ($response instanceof Response) {
            return $response;
        }

        return [
            'data' => [
                'entity' => $role,
                'customizableRole' => $customizableRole
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                UpdateHandler::class,
                CustomerUserRoleUpdateFrontendHandler::class
            ]
        );
    }
}
