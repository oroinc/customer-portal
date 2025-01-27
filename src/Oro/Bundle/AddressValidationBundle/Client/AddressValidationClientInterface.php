<?php

namespace Oro\Bundle\AddressValidationBundle\Client;

use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequestInterface;
use Oro\Bundle\AddressValidationBundle\Client\Response\AddressValidationResponseInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Abstraction for the client of address validation.
 */
interface AddressValidationClientInterface
{
    public function send(
        AddressValidationRequestInterface $request,
        Transport $transport
    ): AddressValidationResponseInterface;
}
