<?php
declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Command;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserApi;
use Oro\Bundle\WsseAuthenticationBundle\Command\GenerateWsseHeaderCommand;

/**
 * Generates X-WSSE HTTP header for a given customer user API key.
 */
class GenerateCustomerUserWsseHeaderCommand extends GenerateWsseHeaderCommand
{
    /** @var string */
    protected static $defaultName = 'oro:customer-user:wsse:generate-header';

    public function configure()
    {
        parent::configure();

        $this->setDescription('Generates X-WSSE HTTP header for a given customer user API key.');
    }

    protected function getApiKeyEntityClass(): string
    {
        return CustomerUserApi::class;
    }

    protected function getDefaultSecurityFirewall(): string
    {
        return 'frontend_api_wsse_secured';
    }
}
