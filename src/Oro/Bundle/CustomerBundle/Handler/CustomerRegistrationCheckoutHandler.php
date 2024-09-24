<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CheckoutBundle\Handler\CheckoutHandlerInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles submit of frontend customer registration form during checkout
 */
class CustomerRegistrationCheckoutHandler implements CheckoutHandlerInterface
{
    public function __construct(
        private CustomerRegistrationHandlerInterface $registrationHandler,
        private CheckoutHandlerInterface $checkoutGetHandler
    ) {
    }

    #[\Override]
    public function isSupported(Request $request): bool
    {
        return $request->isMethod(Request::METHOD_POST) && $this->registrationHandler->isRegistrationRequest($request);
    }

    #[\Override]
    public function handle(WorkflowItem $workflowItem, Request $request): void
    {
        $this->registrationHandler->handleRegistration($request);
        $form = $this->registrationHandler->getForm();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->checkoutGetHandler->handle($workflowItem, $request);
        }
    }
}
