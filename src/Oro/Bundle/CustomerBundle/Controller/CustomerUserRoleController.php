<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Model\PrivilegeCategory;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCapabilityProvider;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CustomerUserRoleController extends Controller
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
            'entity_class' => $this->container->getParameter('oro_customer.entity.customer_user_role.class')
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
                'data' => $this->getTabListOptions()
            ],
            'capabilitySetOptions' => [
                'data' => $this->getRolePrivilegeCapabilityProvider()->getCapabilities($role),
                'tabIds' => $this->getRolePrivilegeCategoryProvider()->getTabList(),
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
     * @return array
     */
    public function createAction()
    {
        $roleClass = $this->container->getParameter('oro_customer.entity.customer_user_role.class');

        return $this->update(new $roleClass());
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
     * @param CustomerUserRole $role
     * @return array
     */
    public function updateAction(CustomerUserRole $role)
    {
        return $this->update($role);
    }

    /**
     * @param CustomerUserRole $role
     * @return array|RedirectResponse
     */
    protected function update(CustomerUserRole $role)
    {
        $handler = $this->get('oro_customer.form.handler.update_customer_user_role');
        $handler->createForm($role);

        if ($handler->process($role)) {
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
                    'data' => $this->getTabListOptions()
                ],
                'capabilitySetOptions' => [
                    'data' => $this->getRolePrivilegeCapabilityProvider()->getCapabilities($role),
                    'tabIds' => $this->getRolePrivilegeCategoryProvider()->getTabList()
                ]
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

    /**
     * @return array
     */
    protected function getTabListOptions()
    {
        return array_map(
            function (PrivilegeCategory $tab) {
                return [
                    'id' => $tab->getId(),
                    'label' => $this->get('translator')->trans($tab->getLabel())
                ];
            },
            $this->getRolePrivilegeCategoryProvider()->getTabbedCategories()
        );
    }
}
