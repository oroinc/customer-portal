<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Resolver\Factory;

use Oro\Bundle\AddressValidationBundle\Resolver\AddressValidationResolverInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Interface for an address validation resolver factory.
 */
interface AddressValidationResolverFactoryInterface
{
    public function createForTransport(Transport $transport): AddressValidationResolverInterface;

    public function isSupported(Transport $transport): bool;
}
