<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

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

    /**
     * @param CustomerUserManager $userManager
     * @param TranslatorInterface $translator
     * @param LoggerInterface     $logger
     */
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
                    $user->setConfirmationToken($user->generateToken());

                    try {
                        $this->userManager->sendResetPasswordEmail($user);
                        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));
                        $this->userManager->updateUser($user);
                        $result = $email;
                    } catch (\Exception $e) {
                        $this->logger->error(
                            'Unable to sent the reset password email.',
                            ['email' => $email, 'exception' => $e]
                        );
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
}
