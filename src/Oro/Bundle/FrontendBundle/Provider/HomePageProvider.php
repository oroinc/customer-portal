<?php

namespace Oro\Bundle\FrontendBundle\Provider;

/**
 * Default implementation of the storefront homepage provider.
 */
class HomePageProvider implements HomePageProviderInterface
{
    #[\Override]
    public function getHomePage(): object
    {
        throw new \BadMethodCallException('An appropriate home page provider should be implemented.');
    }
}
