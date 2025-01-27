<?php

namespace Oro\Bundle\AddressValidationBundle\Cache;

use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequestInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Provides an abstraction for Address Validation cache key.
 */
interface AddressValidationCacheKeyInterface
{
    public function getRequest(): AddressValidationRequestInterface;

    public function getTransport(): Transport;

    public function getCacheKey(): string;
}
