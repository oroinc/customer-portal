<?php

namespace Oro\Bundle\AddressValidationBundle\Client;

use Oro\Bundle\AddressValidationBundle\Cache\AddressValidationCacheKey;
use Oro\Bundle\AddressValidationBundle\Cache\AddressValidationCacheKeyInterface;
use Oro\Bundle\AddressValidationBundle\Cache\AddressValidationResponseCacheInterface;
use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequestInterface;
use Oro\Bundle\AddressValidationBundle\Client\Response\AddressValidationResponseInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Basic implementation for Address Validation cached client.
 */
class AddressValidationCachedClient implements AddressValidationClientInterface
{
    public function __construct(
        private AddressValidationClientInterface $client,
        private AddressValidationResponseCacheInterface $cache,
    ) {
    }

    public function send(
        AddressValidationRequestInterface $request,
        Transport $transport
    ): AddressValidationResponseInterface {
        $cacheKey = $this->getAddressValidationCacheKey($request, $transport);

        $response = $this->cache->get($cacheKey);
        if ($response) {
            return $response;
        }

        $response = $this->client->send($request, $transport);
        if ($response->isSuccessful()) {
            $this->cache->set($cacheKey, $response);
        }

        return $response;
    }

    private function getAddressValidationCacheKey(
        AddressValidationRequestInterface $request,
        Transport $transport
    ): AddressValidationCacheKeyInterface {
        return new AddressValidationCacheKey($request, $transport);
    }
}
