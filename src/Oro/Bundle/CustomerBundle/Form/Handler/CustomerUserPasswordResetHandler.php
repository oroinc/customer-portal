<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles reset password request.
 */
class CustomerUserPasswordResetHandler
{
    private CustomerUserManager $userManager;
    private LoggerInterface $logger;

    public function __construct(CustomerUserManager $userManager, LoggerInterface $logger)
    {
        $this->userManager = $userManager;
        $this->logger = $logger;
    }

    public function process(FormInterface $form, Request $request): bool
    {
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                /** @var CustomerUser $customerUser */
                $customerUser = $form->getData();
                if ($form->isValid()) {
                    $customerUser
                        ->setConfirmed(true)
                        ->setConfirmationToken(null)
                        ->setPasswordRequestedAt(null);

                    $this->userManager->setAuthStatus($customerUser, CustomerUserManager::STATUS_ACTIVE);
                    $this->userManager->updateUser($customerUser);

                    $this->logger->notice(
                        'Password was successfully reset for customer user.',
                        ['user_id' => $customerUser->getId()]
                    );

                    return true;
                }

                $this->logger->notice(
                    'Password reset for customer user was failed.',
                    ['user_id' => $customerUser->getId()]
                );
            }
        }

        return false;
    }
}
