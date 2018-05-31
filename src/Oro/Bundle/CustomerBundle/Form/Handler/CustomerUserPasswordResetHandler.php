<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerUserPasswordResetHandler extends AbstractCustomerUserPasswordHandler
{
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
