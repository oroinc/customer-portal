<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;

/**
 * Returns message should be shown to user after registration.
 */
class RegistrationSuccessMessageProvider implements RegistrationSuccessMessageProviderInterface
{
    public function __construct(
        private CustomerUserManager $customerUserManager
    ) {
    }

    public function getRegistrationSuccessMessage(): string
    {
        $registrationMessage = 'oro.customer.controller.customeruser.registered.message';
        if ($this->customerUserManager->isConfirmationRequired()) {
            $registrationMessage = 'oro.customer.controller.customeruser.registered_with_confirmation.message';
        }

        return $registrationMessage;
    }
}
