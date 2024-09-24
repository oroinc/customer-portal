<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserRoleUpdateHandler;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCapabilityProvider;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD controller for CustomerUserRole entity
 */
class CustomerUserRoleController extends AbstractController
{
    /**
     *
     * @return array
     */
    #[Route(path: '/', name: 'oro_customer_customer_user_role_index')]
    #[Template]
    #[AclAncestor('oro_customer_customer_user_role_view')]
    public function indexAction()
    {
        return [
            'entity_class' => CustomerUserRole::class
        ];
    }

    /**
     * @param CustomerUserRole $role
     * @return array
     */
    #[Route(path: '/view/{id}', name: 'oro_customer_customer_user_role_view', requirements: ['id' => '\d+'])]
    #[Template]
    #[Acl(
        id: 'oro_customer_customer_user_role_view',
        type: 'entity',
        class: CustomerUserRole::class,
        permission: 'VIEW'
    )]
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
     * @param Request $request
     * @return array
     */
    #[Route(path: '/create', name: 'oro_customer_customer_user_role_create')]
    #[Template('@OroCustomer/CustomerUserRole/update.html.twig')]
    #[Acl(
        id: 'oro_customer_customer_user_role_create',
        type: 'entity',
        class: CustomerUserRole::class,
        permission: 'CREATE'
    )]
    public function createAction(Request $request)
    {
        return $this->update(new CustomerUserRole(), $request);
    }

    /**
     *
     * @param Request $request
     * @param CustomerUserRole $role
     * @return array
     */
    #[Route(path: '/update/{id}', name: 'oro_customer_customer_user_role_update', requirements: ['id' => '\d+'])]
    #[Template]
    #[Acl(
        id: 'oro_customer_customer_user_role_update',
        type: 'entity',
        class: CustomerUserRole::class,
        permission: 'EDIT'
    )]
    public function updateAction(Request $request, CustomerUserRole $role)
    {
        return $this->update($role, $request);
    }

    /**
     * @param CustomerUserRole $role
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function update(CustomerUserRole $role, Request $request)
    {
        $handler = $this->container->get(CustomerUserRoleUpdateHandler::class);
        $handler->createForm($role);
        $isWidgetContext = (bool)$request->get('_wid', false);

        if ($handler->process($role) && !$isWidgetContext) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)
                    ->trans('oro.customer.controller.customeruserrole.saved.message')
            );

            return $this->container->get(Router::class)->redirect($role);
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

    protected function getRolePrivilegeCategoryProvider(): RolePrivilegeCategoryProvider
    {
        return $this->container->get(RolePrivilegeCategoryProvider::class);
    }

    protected function getRolePrivilegeCapabilityProvider(): RolePrivilegeCapabilityProvider
    {
        return $this->container->get(RolePrivilegeCapabilityProvider::class);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                Router::class,
                RolePrivilegeCategoryProvider::class,
                RolePrivilegeCapabilityProvider::class,
                CustomerUserRoleUpdateHandler::class,
            ]
        );
    }
}
