<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserRoleUpdateFrontendHandler;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
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
    #[Route(path: '/', name: 'oro_customer_frontend_customer_user_role_index')]
    #[Layout(vars: ['entity_class'])]
    #[Acl(
        id: 'oro_customer_frontend_customer_user_role_index',
        type: 'entity',
        class: CustomerUserRole::class,
        permission: 'VIEW',
        groupName: 'commerce'
    )]
    public function indexAction(): array
    {
        return [
            'entity_class' => CustomerUserRole::class,
        ];
    }

    #[Route(path: '/view/{id}', name: 'oro_customer_frontend_customer_user_role_view', requirements: ['id' => '\d+'])]
    #[Layout]
    public function viewAction(CustomerUserRole $role): array
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

    #[Route(path: '/create', name: 'oro_customer_frontend_customer_user_role_create')]
    #[Layout]
    #[Acl(
        id: 'oro_customer_frontend_customer_user_role_create',
        type: 'entity',
        class: CustomerUserRole::class,
        permission: 'CREATE',
        groupName: 'commerce'
    )]
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new CustomerUserRole());
    }

    #[Route(
        path: '/update/{id}',
        name: 'oro_customer_frontend_customer_user_role_update',
        requirements: ['id' => '\d+']
    )]
    #[Layout]
    public function updateAction(CustomerUserRole $role, Request $request): array|RedirectResponse
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
                $this->container->get(TranslatorInterface::class)
                    ->trans('oro.customer.customeruserrole.frontend.edit-predifined-role.message')
            );
        }

        return $this->update($role);
    }

    protected function update(CustomerUserRole $role): array|RedirectResponse
    {
        $handler = $this->container->get(CustomerUserRoleUpdateFrontendHandler::class);
        $form = $handler->createForm($role);

        // This is cloned role in case of original role was predefined
        $customizableRole = $form->getData();

        $response = $this->container->get(UpdateHandlerFacade::class)->update(
            $customizableRole,
            $form,
            $this->container->get(TranslatorInterface::class)
                ->trans('oro.customer.controller.customeruserrole.saved.message'),
            null,
            function (CustomerUserRole $role) use ($handler) {
                return $handler->process($role);
            }
        );

        if ($response instanceof Response) {
            return $response;
        }

        return [
            'data' => [
                'entity' => $role,
                'customizableRole' => $customizableRole,
                'input_action' => \json_encode([
                    'route' => 'oro_customer_frontend_customer_user_role_view',
                    'params' => ['id' => '$id']
                ])
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                CustomerUserRoleUpdateFrontendHandler::class,
                UpdateHandlerFacade::class
            ]
        );
    }
}
