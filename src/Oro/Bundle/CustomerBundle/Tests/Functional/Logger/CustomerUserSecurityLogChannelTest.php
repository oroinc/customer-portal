<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Logger;

use Oro\Bundle\LoggerBundle\Tests\Functional\Logger\DbLogsHandlerTestCase;

class CustomerUserSecurityLogChannelTest extends DbLogsHandlerTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getLogChannelName(): string
    {
        return 'oro_customer_user_security';
    }
}
