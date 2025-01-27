<?php

namespace Oro\Bundle\AddressValidationBundle\Cache;

use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequestInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Basic implementation for Address Validation cache key.
 */
class AddressValidationCacheKey implements AddressValidationCacheKeyInterface
{
    public function __construct(
        protected AddressValidationRequestInterface $request,
        protected Transport $transport
    ) {
    }

    public function getRequest(): AddressValidationRequestInterface
    {
        return $this->request;
    }

    public function getTransport(): Transport
    {
        return $this->transport;
    }

    public function getCacheKey(): string
    {
        return (string) crc32(serialize($this->request->getRequestData()));
    }
}
