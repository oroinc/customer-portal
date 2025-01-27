<?php

namespace Oro\Bundle\AddressValidationBundle\Exception;

/**
 * Thrown when a {@see Transport} is not supported by any address validation resolver.
 */
class TransportNotSupportedException extends \LogicException
{
}
