<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\CustomerUserEvents;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Event\FilterCustomerUserResponseEvent;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserInvitationAcceptHandler;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserInvitationHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserInvitationFormProvider;
use Oro\Bundle\CustomerBundle\Manager\CustomerUserInvitationManager;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles storefront customer user invitations.
 */
class CustomerUserInvitationController extends AbstractController
{
    #[Route(path: '/create', name: 'oro_customer_frontend_customer_user_invitation_create')]
    #[Layout]
    #[Acl(
        id: 'oro_customer_frontend_customer_user_invitation_create',
        type: 'entity',
        class: CustomerUserInvitation::class,
        permission: 'CREATE',
        groupName: 'commerce'
    )]
    public function createAction(Request $request): array|RedirectResponse
    {
        $this->denyAccessUnlessGranted('CREATE', 'entity:commerce@' . CustomerUser::class);
        $this->denyAccessUnlessGranted('oro_customer_frontend_customer_user_role_view');

        $website = $this->container->get(WebsiteManager::class)->getCurrentWebsite();
        if (!$website) {
            throw $this->createNotFoundException('Website was not found.');
        }
        $customerUser = $this->getUser();
        $invitation = (new CustomerUserInvitation())
            ->setOrganization($customerUser->getOrganization())
            ->setWebsite($website)
            ->setCustomer($customerUser->getCustomer())
            ->setInvitedBy($customerUser);
        $form = $this->container->get(FrontendCustomerUserInvitationFormProvider::class)
            ->getInvitationForm($invitation);
        $handler = new CustomerUserInvitationHandler(
            $this->container->get(CustomerUserInvitationManager::class),
            $this->container->get(TranslatorInterface::class),
            $this->container->get(LoggerInterface::class)
        );
        $result = $this->container->get(UpdateHandlerFacade::class)->update(
            $invitation,
            $form,
            null,
            $request,
            $handler
        );
        if ($result instanceof RedirectResponse) {
            return $this->redirectToRoute('oro_customer_frontend_customer_user_index');
        }

        return ['data' => ['entity' => $invitation]];
    }

    #[Route(
        path: '/accept/{token}',
        name: 'oro_customer_frontend_customer_user_invitation_accept'
    )]
    #[Layout(action: 'oro_customer_frontend_customer_user_register')]
    public function acceptAction(string $token, Request $request): array|RedirectResponse
    {
        // redirect if already logged in customer user
        if ($this->getUser() instanceof CustomerUser) {
            $request->getSession()->getFlashBag()->add(
                'warning',
                $this->container->get(TranslatorInterface::class)
                    ->trans('oro.customer.customeruserinvitation.message.sign_out_first')
            );

            return $this->redirectToRoute('oro_customer_frontend_customer_user_profile');
        }

        $manager = $this->container->get(CustomerUserInvitationManager::class);
        $invitation = $manager->findByToken($token);
        if (!$invitation || !$manager->isUsable($invitation)) {
            return $this->redirectInvalidInvitation($request);
        }
        try {
            $customerUser = $manager->createCustomerUser($invitation);
        } catch (\DomainException | \LogicException $exception) {
            $this->container->get(LoggerInterface::class)->warning(
                'Customer user invitation could not be prepared for acceptance.',
                ['exception' => $exception, 'invitation_id' => $invitation->getId()]
            );

            return $this->redirectInvalidInvitation($request);
        }
        $formProvider = $this->container->get(FrontendCustomerUserInvitationFormProvider::class);
        $form = $formProvider->getAcceptForm($customerUser, $token);
        $handler = new CustomerUserInvitationAcceptHandler($manager, $invitation, $token);
        try {
            $result = $this->container->get(UpdateHandlerFacade::class)->update(
                $customerUser,
                $form,
                $this->container->get(TranslatorInterface::class)
                    ->trans('oro.customer.customeruserinvitation.message.accepted'),
                $request,
                $handler
            );
        } catch (\DomainException | \LogicException $exception) {
            $this->container->get(LoggerInterface::class)->warning(
                'Customer user invitation could not be accepted.',
                ['exception' => $exception, 'invitation_id' => $invitation->getId()]
            );

            return $this->redirectInvalidInvitation($request);
        }
        if ($result instanceof RedirectResponse) {
            $response = $this->redirectToRoute('oro_customer_customer_user_security_login');

            $this->container->get(EventDispatcherInterface::class)->dispatch(
                new FilterCustomerUserResponseEvent($customerUser, $request, $response),
                CustomerUserEvents::INVITATION_ACCEPTED
            );

            return $response;
        }

        return [
            'data' => [
                'entity' => $invitation,
                'customerUser' => $customerUser,
                'token' => $token
            ]
        ];
    }

    private function redirectInvalidInvitation(Request $request): RedirectResponse
    {
        $request->getSession()->getFlashBag()->add(
            'error',
            $this->container->get(TranslatorInterface::class)
                ->trans('oro.customer.customeruserinvitation.message.invalid')
        );

        return $this->redirectToRoute('oro_customer_customer_user_security_login');
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            FrontendCustomerUserInvitationFormProvider::class,
            CustomerUserInvitationManager::class,
            UpdateHandlerFacade::class,
            WebsiteManager::class,
            TranslatorInterface::class,
            LoggerInterface::class,
            EventDispatcherInterface::class
        ]);
    }
}
