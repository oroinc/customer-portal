<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles reset password request.
 */
class CustomerUserPasswordResetHandler
{
    /** @var CustomerUserManager */
    private $userManager;

    public function __construct(CustomerUserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return bool
     */
    public function process(FormInterface $form, Request $request)
    {
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var CustomerUser $user */
                $user = $form->getData();

                $user
                    ->setConfirmed(true)
                    ->setConfirmationToken(null)
                    ->setPasswordRequestedAt(null);

                $this->userManager->updateUser($user);

                return true;
            }
        }

        return false;
    }
}
