<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCapabilityProvider;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD controller for CustomerUserRole entity
 */
class CustomerUserRoleController extends AbstractController
{
    /**
     * @Route("/", name="oro_customer_customer_user_role_index")
     * @Template
     * @AclAncestor("oro_customer_customer_user_role_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => CustomerUserRole::class
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_customer_customer_user_role_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_customer_customer_user_role_view",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUserRole",
     *      permission="VIEW"
     * )
     *
     * @param CustomerUserRole $role
     * @return array
     */
    public function viewAction(CustomerUserRole $role)
    {
        return [
            'entity' => $role,
            'tabsOptions' => [
                'data' => $this->getRolePrivilegeCategoryProvider()->getTabs()
            ],
            'capabilitySetOptions' => [
                'data' => $this->getRolePrivilegeCapabilityProvider()->getCapabilities($role),
                'tabIds' => $this->getRolePrivilegeCategoryProvider()->getTabIds(),
                'readonly' => true
            ]
        ];
    }

    /**
     * @Route("/create", name="oro_customer_customer_user_role_create")
     * @Template("OroCustomerBundle:CustomerUserRole:update.html.twig")
     * @Acl(
     *      id="oro_customer_customer_user_role_create",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUserRole",
     *      permission="CREATE"
     * )
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        $roleClass = CustomerUserRole::class;

        return $this->update($request, new $roleClass());
    }

    /**
     * @Route("/update/{id}", name="oro_customer_customer_user_role_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_customer_customer_user_role_update",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUserRole",
     *      permission="EDIT"
     * )
     *
     * @param Request $request
     * @param CustomerUserRole $role
     * @return array
     */
    public function updateAction(Request $request, CustomerUserRole $role)
    {
        return $this->update($request, $role);
    }

    /**
     * @param Request $request
     * @param CustomerUserRole $role
     * @return array|RedirectResponse
     */
    protected function update(Request $request, CustomerUserRole $role)
    {
        $handler = $this->get('oro_customer.form.handler.update_customer_user_role');
        $handler->createForm($role);
        $isWidgetContext = (bool)$request->get('_wid', false);

        if ($handler->process($role) && !$isWidgetContext) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.customer.controller.customeruserrole.saved.message')
            );

            return $this->get('oro_ui.router')->redirect($role);
        } else {
            return [
                'entity' => $role,
                'form' => $handler->createView(),
                'tabsOptions' => [
                    'data' => $this->getRolePrivilegeCategoryProvider()->getTabs()
                ],
                'capabilitySetOptions' => [
                    'data' => $this->getRolePrivilegeCapabilityProvider()->getCapabilities($role),
                    'tabIds' => $this->getRolePrivilegeCategoryProvider()->getTabIds()
                ],
                'isWidgetContext' => $isWidgetContext,
                'savedId' => $role->getId(),
            ];
        }
    }

    /**
     * @return RolePrivilegeCategoryProvider
     */
    protected function getRolePrivilegeCategoryProvider()
    {
        return $this->get('oro_user.provider.role_privilege_category_provider');
    }

    /**
     * @return RolePrivilegeCapabilityProvider
     */
    protected function getRolePrivilegeCapabilityProvider()
    {
        return $this->get('oro_user.provider.role_privilege_capability_provider_commerce');
    }
}
