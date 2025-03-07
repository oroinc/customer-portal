<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRegistrationFormProvider;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles submit of frontend customer registration form
 */
class CustomerRegistrationHandler implements CustomerRegistrationHandlerInterface
{
    /**
     * @var FrontendCustomerUserRegistrationFormProvider
     */
    private $formProvider;

    /**
     * @var CustomerUserManager
     */
    private $customerUserManager;

    /**
     * @var FrontendCustomerUserHandler
     */
    private $customerUserHandler;

    /**
     * @var UpdateHandlerFacade
     */
    private $updateHandler;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FormInterface
     */
    private $form;

    public function __construct(
        FrontendCustomerUserRegistrationFormProvider $formProvider,
        CustomerUserManager $customerUserManager,
        FrontendCustomerUserHandler $customerUserHandler,
        UpdateHandlerFacade $updateHandler,
        TranslatorInterface $translator
    ) {
        $this->formProvider = $formProvider;
        $this->customerUserManager = $customerUserManager;
        $this->customerUserHandler = $customerUserHandler;
        $this->updateHandler = $updateHandler;
        $this->translator = $translator;
    }

    #[\Override]
    public function handleRegistration(Request $request): array|RedirectResponse
    {
        $form = $this->getForm();

        $registrationMessage = 'oro.customer.controller.customeruser.registered.message';
        if ($this->customerUserManager->isConfirmationRequired()) {
            $registrationMessage = 'oro.customer.controller.customeruser.registered_with_confirmation.message';
        }

        return $this->updateHandler->update(
            $form->getData(),
            $form,
            $this->translator->trans($registrationMessage),
            $request,
            $this->customerUserHandler
        );
    }

    #[\Override]
    public function isRegistrationRequest(Request $request): bool
    {
        return (bool) $request->query->get('isRegistration');
    }

    #[\Override]
    public function getForm(): FormInterface
    {
        if (null === $this->form) {
            $this->form = $this->formProvider->getRegisterForm();
        }

        return $this->form;
    }
}
