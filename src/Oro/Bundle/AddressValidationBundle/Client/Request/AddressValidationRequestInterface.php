<?php

namespace Oro\Bundle\AddressValidationBundle\Client\Request;

/**
 * Abstraction for the address validation request.
 */
interface AddressValidationRequestInterface
{
    public function getUri(): string;

    public function getRequestData(): array;

    public function isCheckMode(): bool;
}
