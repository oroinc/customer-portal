<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Form\DataTransformer;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\AddressValidationBundle\ResolvedAddress\ResolvedAddressAcceptorInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms the selected address by accepting the {@see ResolvedAddress} against the original address.
 */
class ResolvedAddressAcceptingTransformer implements DataTransformerInterface
{
    public function __construct(
        private ResolvedAddressAcceptorInterface $resolvedAddressAcceptor
    ) {
    }

    public function transform(mixed $value): ?AbstractAddress
    {
        return $value;
    }

    public function reverseTransform(mixed $value): ?AbstractAddress
    {
        if (!$value instanceof ResolvedAddress) {
            return $value;
        }

        return $this->resolvedAddressAcceptor->acceptResolvedAddress($value);
    }
}
