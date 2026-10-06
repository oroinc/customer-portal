<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserInvitationHandler;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserInvitationType;
use Oro\Bundle\CustomerBundle\Manager\CustomerUserInvitationManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Back-office pages for customer user invitations.
 */
class CustomerUserInvitationController extends AbstractController
{
    #[Route(path: '/', name: 'oro_customer_customer_user_invitation_index')]
    #[Template('@OroCustomer/CustomerUserInvitation/index.html.twig')]
    #[Acl(
        id: 'oro_customer_customer_user_invitation_view',
        type: 'entity',
        class: CustomerUserInvitation::class,
        permission: 'VIEW'
    )]
    public function indexAction(): array
    {
        return ['entity_class' => CustomerUserInvitation::class];
    }

    #[Route(
        path: '/get-roles/{customerId}',
        name: 'oro_customer_customer_user_invitation_roles',
        requirements: ['customerId' => '\d+'],
        defaults: ['customerId' => 0]
    )]
    #[Template('@OroCustomer/CustomerUserInvitation/widget/roles.html.twig')]
    #[AclAncestor('oro_customer_customer_user_invitation_create')]
    public function getRolesAction(Request $request, string $customerId): array
    {
        $invitation = new CustomerUserInvitation();
        if ($customerId) {
            $invitation->setCustomer(
                $this->container->get(DoctrineHelper::class)->getEntityReference(Customer::class, $customerId)
            );
        }
        $form = $this->createForm(CustomerUserInvitationType::class, $invitation);
        $form->handleRequest($this->container->get(RequestStack::class)->getMainRequest());
        if (($error = $request->get('error', '')) && $form->has('roles')) {
            $form->get('roles')->addError(new FormError((string)$error));
        }

        return ['form' => $form->createView()];
    }

    #[Route(path: '/create', name: 'oro_customer_customer_user_invitation_create')]
    #[Template('@OroCustomer/CustomerUserInvitation/update.html.twig')]
    #[Acl(
        id: 'oro_customer_customer_user_invitation_create',
        type: 'entity',
        class: CustomerUserInvitation::class,
        permission: 'CREATE'
    )]
    public function createAction(Request $request): array|RedirectResponse
    {
        $invitation = new CustomerUserInvitation();
        $form = $this->createForm(CustomerUserInvitationType::class, $invitation);
        $handler = new CustomerUserInvitationHandler(
            $this->container->get(CustomerUserInvitationManager::class),
            $this->container->get(TranslatorInterface::class),
            $this->container->get(LoggerInterface::class),
            true
        );
        $result = $this->container->get(UpdateHandlerFacade::class)->update(
            $invitation,
            $form,
            null,
            $request,
            $handler
        );

        return $result instanceof RedirectResponse
            ? $this->redirectToRoute('oro_customer_customer_user_invitation_index')
            : $result;
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            CustomerUserInvitationManager::class,
            DoctrineHelper::class,
            RequestStack::class,
            TranslatorInterface::class,
            LoggerInterface::class,
            UpdateHandlerFacade::class
        ]);
    }
}
