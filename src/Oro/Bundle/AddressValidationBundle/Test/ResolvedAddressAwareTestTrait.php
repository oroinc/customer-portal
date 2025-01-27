<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Test;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;

/**
 * Provides handy methods for functional tests working with {@see ResolvedAddress}.
 *
 * @method static getContainer(): ContainerInterface
 */
trait ResolvedAddressAwareTestTrait
{
    private static function createResolvedAddress(
        AbstractAddress $address,
        AbstractAddress $originalAddress
    ): ResolvedAddress {
        $resolvedAddress = new ResolvedAddress($originalAddress);

        self::getContainer()
            ->get('oro_customer.utils.address_copier')
            ->copyToAddress($address, $resolvedAddress);

        return $resolvedAddress;
    }
}
