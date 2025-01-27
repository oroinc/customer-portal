<?php

namespace Oro\Bundle\AddressValidationBundle\Client\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * Basic implementation of response of Address Validation Rest API.
 */
class AddressValidationResponse implements AddressValidationResponseInterface
{
    public function __construct(
        private int $responseStatusCode = Response::HTTP_OK,
        private array $resolvedAddresses = [],
        private array $errors = []
    ) {
    }

    #[\Override]
    public function getResponseStatusCode(): int
    {
        return $this->responseStatusCode;
    }

    #[\Override]
    public function getResolvedAddresses(): array
    {
        return $this->resolvedAddresses;
    }

    #[\Override]
    public function isSuccessful(): bool
    {
        return $this->getResponseStatusCode() === Response::HTTP_OK;
    }

    #[\Override]
    public function getErrors(): array
    {
        return $this->errors;
    }
}
