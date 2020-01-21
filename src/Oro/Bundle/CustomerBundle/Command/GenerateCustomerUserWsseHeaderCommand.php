<?php

namespace Oro\Bundle\CustomerBundle\Command;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserApi;
use Oro\Bundle\WsseAuthenticationBundle\Command\GenerateWsseHeaderCommand;

/**
 * Generate X-WSSE HTTP header for a given customer user API key.
 */
class GenerateCustomerUserWsseHeaderCommand extends GenerateWsseHeaderCommand
{
    /** @var string */
    protected static $defaultName = 'oro:customer-user:wsse:generate-header';

    /**
     * {@inheritDoc}
     */
    protected function getApiKeyEntityClass(): string
    {
        return CustomerUserApi::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultSecurityFirewall(): string
    {
        return 'frontend_api_wsse_secured';
    }
}
