<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Resolver\Factory;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\AddressValidationBundle\Exception\TransportNotSupportedException;
use Oro\Bundle\AddressValidationBundle\Resolver\AddressValidationResolverInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Creates an address validation resolver by delegating a call to inner factories.
 */
class AddressValidationResolverChainFactory implements AddressValidationResolverFactoryInterface
{
    /**
     * @param iterable<AddressValidationResolverFactoryInterface> $innerFactories
     */
    public function __construct(private iterable $innerFactories)
    {
    }

    public function createForTransport(Transport $transport): AddressValidationResolverInterface
    {
        foreach ($this->innerFactories as $innerFactory) {
            if ($innerFactory->isSupported($transport)) {
                return $innerFactory->createForTransport($transport);
            }
        }

        throw new TransportNotSupportedException(
            sprintf(
                'Transport %s #%s is not supported by any %s',
                ClassUtils::getClass($transport),
                $transport->getId(),
                AddressValidationResolverFactoryInterface::class
            )
        );
    }

    public function isSupported(Transport $transport): bool
    {
        foreach ($this->innerFactories as $innerFactory) {
            if ($innerFactory->isSupported($transport)) {
                return true;
            }
        }

        return false;
    }
}
