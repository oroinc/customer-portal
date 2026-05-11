<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\ChangeCustomerUserEmailHandler;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\UIBundle\Tools\FlashMessageHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles Customer user email change actions.
 */
class CustomerUserEmailChangeController extends AbstractController
{
    #[Route(path: '/confirm-email-change', name: 'oro_customer_frontend_customer_user_confirm_old_email')]
    #[Layout]
    #[AclAncestor('oro_customer_frontend_update_own_profile')]
    public function confirmOldEmailAction(Request $request): array
    {
        $this->checkAccess($request);

        $customerUser = $this->getUser();

        return [
            'data' => [
                'entity' => $this->getUser(),
                'backToUrl' => $this->container->get('router')->generate('oro_customer_frontend_customer_user_profile'),
                'confirmUrl' => $this->container->get('router')->generate(
                    'oro_customer_frontend_customer_user_confirmed_old_email',
                    ['token' => $customerUser->getNewEmailVerificationCode()]
                ),
                'cancelUrl' => $this->container->get('router')->generate(
                    'oro_customer_frontend_customer_user_cancel',
                    ['token' => $customerUser->getNewEmailVerificationCode()]
                )
            ]
        ];
    }

    #[Route(path: '/confirmed-old-email', name: 'oro_customer_frontend_customer_user_confirmed_old_email')]
    #[AclAncestor('oro_customer_frontend_update_own_profile')]
    public function confirmedOldEmailAction(Request $request): RedirectResponse
    {
        $this->checkAccess($request);

        $customerUser = $this->getUser();
        $emailChangeHandler = $this->container->get('oro_customer.customer_user.email_change_handler');

        $featureChecker = $this->container->get(FeatureChecker::class);
        if (!$featureChecker->isFeatureEnabled('oro_customer_confirmation_required')) {
            return $this->redirectToRoute(
                'oro_customer_frontend_customer_user_confirm_new_email',
                ['token' => $customerUser->getNewEmailVerificationCode()]
            );
        }

        $message = 'oro.customer.customeruser.profile.email_change.message_send.success';
        if (!$emailChangeHandler->sendEmailToNewEmail($customerUser)) {
            $message = 'oro.customer.customeruser.profile.email_change.message_send.failed';
        }
        $this->container->get(FlashMessageHelper::class)
            ->addFlashMessage(
                'info',
                $message,
                ['%new_email%' => $customerUser->getNewEmail()]
            );

        return $this->redirectToRoute('oro_customer_frontend_customer_user_profile');
    }

    #[Route(path: '/cancel', name: 'oro_customer_frontend_customer_user_cancel')]
    #[AclAncestor('oro_customer_frontend_update_own_profile')]
    public function cancelAction(Request $request): RedirectResponse
    {
        $this->checkAccess($request);

        $emailChangeHandler = $this->container->get('oro_customer.customer_user.email_change_handler');
        $emailChangeHandler->cancelEmailChange($this->getUser());

        $this->container->get(FlashMessageHelper::class)
            ->addFlashMessage(
                'info',
                'oro.customer.customeruser.profile.email_change.canceled',
                []
            );

        return $this->redirectToRoute('oro_customer_frontend_customer_user_profile');
    }

    #[Route(path: '/cancel_uv/{id}', name: 'oro_customer_frontend_customer_user_cancel_user_view')]
    #[AclAncestor('oro_customer_frontend_customer_user_update')]
    public function cancelOnUserViewAction(CustomerUser $customerUser): RedirectResponse
    {
        $emailChangeHandler = $this->container->get('oro_customer.customer_user.email_change_handler');
        $emailChangeHandler->cancelEmailChange($customerUser);

        $this->container->get(FlashMessageHelper::class)
            ->addFlashMessage(
                'info',
                'oro.customer.customeruser.profile.email_change.canceled',
                []
            );

        return $this->redirectToRoute('oro_customer_frontend_customer_user_view', ['id' => $customerUser->getId()]);
    }

    #[Route(path: '/confirm-new-email', name: 'oro_customer_frontend_customer_user_confirm_new_email')]
    #[AclAncestor('oro_customer_frontend_update_own_profile')]
    public function confirmNewEmailAction(Request $request): RedirectResponse
    {
        $this->checkAccess($request);

        $customerUser = $this->getUser();

        $emailChangeHandler = $this->container->get('oro_customer.customer_user.email_change_handler');
        $emailChangeHandler->confirmNewEmail($this->getUser());

        $this->container->get(FlashMessageHelper::class)
            ->addFlashMessage(
                'info',
                'oro.customer.customeruser.profile.email_change.approved',
                ['%email%' => $customerUser->getEmail()]
            );

        return $this->redirectToRoute('oro_customer_frontend_customer_user_profile');
    }

    private function checkAccess(Request $request): void
    {
        $customerUser = $this->getUser();
        $token = $request->get('token');
        if (
            !$token
            || !$customerUser->getNewEmailVerificationCode()
            || $token !== $customerUser->getNewEmailVerificationCode()
        ) {
            throw $this->createAccessDeniedException();
        }
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'oro_customer.customer_user.email_change_handler' => ChangeCustomerUserEmailHandler::class,
                FeatureChecker::class,
                FlashMessageHelper::class
            ]
        );
    }
}
