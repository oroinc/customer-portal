<?php

namespace Oro\Bundle\AddressValidationBundle\Cache;

use Oro\Bundle\AddressValidationBundle\Client\Response\AddressValidationResponseInterface;

/**
 * Provides an abstraction for Address Validation cache adapter.
 */
interface AddressValidationResponseCacheInterface
{
    public function has(AddressValidationCacheKeyInterface $key): bool;

    public function get(AddressValidationCacheKeyInterface $key): ?AddressValidationResponseInterface;

    public function set(
        AddressValidationCacheKeyInterface $key,
        AddressValidationResponseInterface $response
    ): bool;

    public function delete(AddressValidationCacheKeyInterface $key): bool;

    public function deleteAll(): bool;
}
