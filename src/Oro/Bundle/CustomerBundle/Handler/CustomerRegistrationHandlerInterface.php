<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles submit of frontend customer registration form
 */
interface CustomerRegistrationHandlerInterface
{
    public function handleRegistration(Request $request): array|RedirectResponse;

    public function isRegistrationRequest(Request $request): bool;

    public function getForm(): FormInterface;
}
