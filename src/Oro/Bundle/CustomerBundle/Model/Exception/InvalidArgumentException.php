<?php

namespace Oro\Bundle\CustomerBundle\Model\Exception;

/**
 * Thrown when an invalid argument is provided to a customer bundle operation.
 *
 * This exception extends the standard InvalidArgumentException to provide
 * domain-specific error handling for customer-related operations.
 */
class InvalidArgumentException extends \InvalidArgumentException
{
}
