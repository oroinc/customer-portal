<?php

namespace Oro\Bundle\AddressValidationBundle\Client\Request;

/**
 * Basic implementation of request of Address Validation Rest API.
 */
class AddressValidationRequest implements AddressValidationRequestInterface
{
    public function __construct(
        private string $uri,
        private array $requestData = [],
        private bool $isCheckMode = false
    ) {
    }

    #[\Override]
    public function getUri(): string
    {
        return $this->uri;
    }

    #[\Override]
    public function getRequestData(): array
    {
        return $this->requestData;
    }

    #[\Override]
    public function isCheckMode(): bool
    {
        return $this->isCheckMode;
    }
}
