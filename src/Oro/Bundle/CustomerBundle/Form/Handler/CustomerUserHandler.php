<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Form handler to process entity update/create
 */
class CustomerUserHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    protected CustomerUserManager $userManager;
    protected TokenAccessorInterface $tokenAccessor;
    protected TranslatorInterface $translator;
    protected LoggerInterface $logger;

    public function __construct(
        CustomerUserManager $userManager,
        TokenAccessorInterface $tokenAccessor,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->userManager = $userManager;
        $this->tokenAccessor = $tokenAccessor;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process($customerUser, FormInterface $form, Request $request)
    {
        $isUpdated = false;
        if (\in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($form, $request);

            if ($form->isValid()) {
                if (!$customerUser->getId()) {
                    $this->userManager->updateWebsiteSettings($customerUser);
                    if ($form->get('passwordGenerate')->getData()) {
                        $generatedPassword = $this->userManager->generatePassword(10);
                        $customerUser->setPlainPassword($generatedPassword);
                    }

                    if ($form->get('sendEmail')->getData()) {
                        try {
                            $this->userManager->sendWelcomeRegisteredByAdminEmail($customerUser);
                        } catch (\Exception $ex) {
                            $this->logger->error('Welcome email sending failed.', ['exception' => $ex]);
                            /** @var Session $session */
                            $session = $request->getSession();
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
