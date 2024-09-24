<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\PlatformBundle\Provider\AbstractPageRequestProvider;

/**
 * Provide list of frontend page requests.
 */
class FrontendPageRequestProvider extends AbstractPageRequestProvider
{
    #[\Override]
    public function getRequests(): array
    {
        return [
            $this->createRequest('GET', 'oro_frontend_root')
        ];
    }
}
