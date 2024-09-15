<?php

namespace Oro\Bundle\FrontendBundle\Provider;

/**
 * Represents a service that provides a storefront homepage.
 */
interface HomePageProviderInterface
{
    public function getHomePage(): object;
}
