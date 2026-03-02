<?php

namespace Oro\Bundle\CustomerBundle\Handler;

/**
 * Returns message should be shown to user after registration.
 */
interface RegistrationSuccessMessageProviderInterface
{
    public function getRegistrationSuccessMessage(): string;
}
