<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\PlatformBundle\Provider\AbstractPageRequestProvider;

/**
 * Provide list of customer page requests.
 */
class CustomerPageRequestProvider extends AbstractPageRequestProvider
{
    public function getRequests(): array
    {
        return [
            $this->createRequest('GET', 'oro_customer_customer_user_security_login')
        ];
    }
}
