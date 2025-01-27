<?php

namespace Oro\Bundle\AddressValidationBundle\Client\Response;

/**
 * Abstraction for the address validation response.
 */
interface AddressValidationResponseInterface
{
    public function getResponseStatusCode(): int;

    public function isSuccessful(): bool;

    /** @return string[] */
    public function getErrors(): array;

    public function getResolvedAddresses(): array;
}
