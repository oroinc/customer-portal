<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles forgot password request.
 */
class CustomerUserPasswordRequestHandler
{
    /** @var CustomerUserManager */
    private $userManager;

    /** @var TranslatorInterface */
    private $translator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        CustomerUserManager $userManager,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->userManager = $userManager;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @param FormInterface $form
     * @param Request       $request
     *
     * @return string|null The requested email address for the reset password message
     */
    public function process(FormInterface $form, Request $request)
    {
        $result = null;
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $email = $form->get('email')->getData();

                /** @var CustomerUser|null $user */
                $user = $this->userManager->findUserByUsernameOrEmail($email);
                if ($user) {
                    if ($this->sendResetPasswordEmail($user, $email)) {
                        $this->userManager->updateUser($user);
                        $result = $email;
                    } else {
                        $form->addError(
                            new FormError($this->translator->trans('oro.email.handler.unable_to_send_email'))
                        );
                    }
                } else {
                    $result = $email;
                }
            }
        }

        return $result;
    }

    private function sendResetPasswordEmail(CustomerUser $user, string $email): bool
    {
        $result = true;
        try {
            $this->userManager->sendResetPasswordEmail($user);
        } catch (\Exception $e) {
            $result = false;
            $this->logger->error(
                'Unable to sent the reset password email.',
                ['email' => $email, 'exception' => $e]
            );
        }

        return $result;
    }
}
