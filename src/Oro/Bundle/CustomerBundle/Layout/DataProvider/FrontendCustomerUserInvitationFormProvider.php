<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserInvitationAcceptType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserInvitationType;
use Oro\Bundle\LayoutBundle\Layout\DataProvider\AbstractFormProvider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Provides storefront forms for creating and accepting customer user invitations.
 */
class FrontendCustomerUserInvitationFormProvider extends AbstractFormProvider
{
    private const INVITATION_CREATE_ROUTE = 'oro_customer_frontend_customer_user_invitation_create';
    private const INVITATION_ACCEPT_ROUTE = 'oro_customer_frontend_customer_user_invitation_accept';

    public function getInvitationForm(CustomerUserInvitation $invitation): FormInterface
    {
        return $this->getForm(
            FrontendCustomerUserInvitationType::class,
            $invitation,
            $this->getInvitationFormOptions()
        );
    }

    public function getInvitationFormView(CustomerUserInvitation $invitation): FormView
    {
        return $this->getFormView(
            FrontendCustomerUserInvitationType::class,
            $invitation,
            $this->getInvitationFormOptions()
        );
    }

    public function getAcceptForm(CustomerUser $customerUser, string $token): FormInterface
    {
        return $this->getForm(
            CustomerUserInvitationAcceptType::class,
            $customerUser,
            $this->getAcceptFormOptions($token)
        );
    }

    public function getAcceptFormView(CustomerUser $customerUser, string $token): FormView
    {
        return $this->getFormView(
            CustomerUserInvitationAcceptType::class,
            $customerUser,
            $this->getAcceptFormOptions($token)
        );
    }

    private function getInvitationFormOptions(): array
    {
        return ['action' => $this->generateUrl(self::INVITATION_CREATE_ROUTE)];
    }

    private function getAcceptFormOptions(string $token): array
    {
        return ['action' => $this->generateUrl(self::INVITATION_ACCEPT_ROUTE, ['token' => $token])];
    }
}
