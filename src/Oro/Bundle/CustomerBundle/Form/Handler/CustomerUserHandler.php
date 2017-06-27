<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Psr\Log\LoggerInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class CustomerUserHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var CustomerUserManager */
    protected $userManager;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param CustomerUserManager $userManager
     * @param TokenAccessorInterface $tokenAccessor
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        CustomerUserManager $userManager,
        TokenAccessorInterface $tokenAccessor,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->tokenAccessor = $tokenAccessor;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * Process form
     *
     * @param CustomerUser $customerUser
     * @return bool True on successful processing, false otherwise
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process(CustomerUser $customerUser)
    {
        $isUpdated = false;
        if (in_array($this->request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                if (!$customerUser->getId()) {
                    if ($this->form->get('passwordGenerate')->getData()) {
                        $generatedPassword = $this->userManager->generatePassword(10);
                        $customerUser->setPlainPassword($generatedPassword);
                    }

                    if ($this->form->get('sendEmail')->getData()) {
                        try {
                            $this->userManager->sendWelcomeEmail($customerUser);
                        } catch (\Exception $ex) {
                            $this->logger->error('Welcome email sending failed.', ['exception' => $ex]);
                            /** @var Session $session */
                            $session = $this->request->getSession();
                            $session->getFlashBag()->add(
                                'error',
                                $this->translator
                                    ->trans('oro.customer.controller.customeruser.welcome_failed.message')
                            );
                        }
                    }
                }

                $organization = $this->tokenAccessor->getOrganization();
                if (null !== $organization) {
                    $customerUser->setOrganization($organization);
                }

                $this->userManager->updateUser($customerUser);

                $isUpdated = true;
            }
        }

        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        if ($customerUser->getId() && $customerUser->getId() === $this->tokenAccessor->getUserId()) {
            $this->userManager->reloadUser($customerUser);
        }

        return $isUpdated;
    }
}
