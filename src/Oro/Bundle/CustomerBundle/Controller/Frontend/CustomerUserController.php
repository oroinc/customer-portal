<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserFormProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD controller for CustomerUser entity
 */
class CustomerUserController extends AbstractController
{
    #[Route(path: '/view/{id}', name: 'oro_customer_frontend_customer_user_view', requirements: ['id' => '\d+'])]
    #[Layout]
    #[Acl(
        id: 'oro_customer_frontend_customer_user_view',
        type: 'entity',
        class: CustomerUser::class,
        permission: 'VIEW',
        groupName: 'commerce'
    )]
    public function viewAction(CustomerUser $customerUser): array
    {
        return [
            'data' => [
                'entity' => $customerUser
            ]
        ];
    }

    #[Route(path: '/', name: 'oro_customer_frontend_customer_user_index')]
    #[Layout(vars: ['entity_class'])]
    #[AclAncestor('oro_customer_frontend_customer_user_view')]
    public function indexAction(): array
    {
        return [
            'entity_class' => CustomerUser::class
        ];
    }

    /**
     * Create customer user form
     */
    #[Route(path: '/create', name: 'oro_customer_frontend_customer_user_create')]
    #[Layout]
    #[Acl(
        id: 'oro_customer_frontend_customer_user_create',
        type: 'entity',
        class: CustomerUser::class,
        permission: 'CREATE',
        groupName: 'commerce'
    )]
    public function createAction(Request $request): array|RedirectResponse
    {
        return $this->update(new CustomerUser(), $request);
    }

    /**
     * Edit customer user form
     */
    #[Route(path: '/update/{id}', name: 'oro_customer_frontend_customer_user_update', requirements: ['id' => '\d+'])]
    #[Layout]
    #[Acl(
        id: 'oro_customer_frontend_customer_user_update',
        type: 'entity',
        class: CustomerUser::class,
        permission: 'EDIT',
        groupName: 'commerce'
    )]
    public function updateAction(CustomerUser $customerUser, Request $request): array|RedirectResponse
    {
        return  $this->update($customerUser, $request);
    }

    protected function update(CustomerUser $customerUser, Request $request): array|RedirectResponse
    {
        $form = $this->container->get(FrontendCustomerUserFormProvider::class)
            ->getCustomerUserForm($customerUser);
        $handler = new CustomerUserHandler(
            $this->container->get(CustomerUserManager::class),
            $this->container->get(TokenAccessorInterface::class),
            $this->container->get(TranslatorInterface::class),
            $this->container->get(LoggerInterface::class)
        );
        $handler->setFeatureChecker($this->container->get(FeatureChecker::class));

        $result = $this->container->get(UpdateHandlerFacade::class)->update(
            $customerUser,
            $form,
            $this->container->get(TranslatorInterface::class)
                ->trans('oro.customer.controller.customeruser.saved.message'),
            $request,
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

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                LoggerInterface::class,
                CustomerUserManager::class,
                TokenAccessorInterface::class,
                FrontendCustomerUserFormProvider::class,
                UpdateHandlerFacade::class,
                FeatureChecker::class
            ]
        );
    }
}
