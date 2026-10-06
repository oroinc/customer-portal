<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Manager\CustomerUserInvitationManager;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Completes an invited customer user's account after registration form validation.
 */
class CustomerUserInvitationAcceptHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    public function __construct(
        private readonly CustomerUserInvitationManager $invitationManager,
        private readonly CustomerUserInvitation $invitation,
        private readonly string $token
    ) {
    }

    /** @param CustomerUser $data */
    #[\Override]
    public function process($data, FormInterface $form, Request $request): bool
    {
        if (!\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return false;
        }
        $this->submitPostPutRequest($form, $request);
        if (!$form->isValid()) {
            return false;
        }
        $this->invitationManager->accept($this->invitation, $data, $this->token);

        return true;
    }
}
