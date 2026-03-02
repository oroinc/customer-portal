<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRegistrationFormProvider;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
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
     * @var FormInterface
     */
    private $form;

    public function __construct(
        private FrontendCustomerUserRegistrationFormProvider $formProvider,
        private FormHandlerInterface $customerUserHandler,
        private UpdateHandlerFacade $updateHandler,
        private TranslatorInterface $translator,
        private RegistrationSuccessMessageProviderInterface $registrationSuccessMessageProvider
    ) {
    }

    #[\Override]
    public function handleRegistration(Request $request): array|RedirectResponse
    {
        $form = $this->getForm();

        $registrationMessage = $this->registrationSuccessMessageProvider->getRegistrationSuccessMessage();

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
