<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Manager\CustomerUserInvitationManager;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Processes customer user invitation forms in the back office and storefront.
 */
class CustomerUserInvitationHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    public function __construct(
        private readonly CustomerUserInvitationManager $invitationManager,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
        private readonly bool $validateInviterScope = false
    ) {
    }

    /** @param CustomerUserInvitation $invitation */
    #[\Override]
    public function process($invitation, FormInterface $form, Request $request): bool
    {
        if (!\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return false;
        }
        $this->submitPostPutRequest($form, $request);
        if ($this->validateInviterScope) {
            $this->applyInviterScope($invitation, $form);
        }
        if (!$form->isValid()) {
            return false;
        }
        try {
            $this->invitationManager->invite($invitation);
        } catch (\DomainException $exception) {
            $this->logger->warning('Customer user invitation could not be created.', [
                'exception' => $exception,
                'customer_id' => $invitation->getCustomer()?->getId()
            ]);
            $form->get('email')->addError(new FormError(
                $this->translator->trans('oro.customer.customeruserinvitation.error.cannot_invite')
            ));

            return false;
        } catch (\RuntimeException $exception) {
            $this->logger->error('Customer user invitation could not be sent.', [
                'exception' => $exception,
                'invitation_id' => $invitation->getId()
            ]);
            $this->addFlash($request, 'warning', 'oro.customer.customeruserinvitation.message.saved_email_failed');

            return true;
        }
        $this->addFlash($request, 'success', 'oro.customer.customeruserinvitation.message.sent');

        return true;
    }

    private function applyInviterScope(CustomerUserInvitation $invitation, FormInterface $form): void
    {
        $invitedBy = $invitation->getInvitedBy();
        if (!$invitedBy instanceof CustomerUser) {
            return;
        }
        $customer = $invitation->getCustomer();
        $inviterCustomer = $invitedBy->getCustomer();
        if (!$customer || !$inviterCustomer || $customer->getId() !== $inviterCustomer->getId()) {
            $form->get('invitedBy')->addError(new FormError(
                $this->translator->trans('oro.customer.customeruserinvitation.error.inviter_customer_mismatch')
            ));

            return;
        }
        if (!$invitedBy->getOrganization() || !$invitedBy->getWebsite() || $invitedBy->isGuest()) {
            $form->get('invitedBy')->addError(new FormError(
                $this->translator->trans('oro.customer.customeruserinvitation.error.invalid_inviter')
            ));

            return;
        }
        $invitation
            ->setOrganization($invitedBy->getOrganization())
            ->setWebsite($invitedBy->getWebsite());
    }

    private function addFlash(Request $request, string $type, string $message): void
    {
        $request->getSession()->getFlashBag()->add($type, $this->translator->trans($message));
    }
}
