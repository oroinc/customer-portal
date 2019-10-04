<?php

namespace Oro\Bundle\FrontendBundle\Request;

/**
 * This class is used to substitute FrontendHelper during installation of the application.
 * It is supposed that all requests are management console requests until the installation is finished.
 * @see \Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension::configureFrontendHelper
 */
class NotInstalledFrontendHelper extends FrontendHelper
{
    public function __construct()
    {
        // no any parameters are required for this class
    }

    /**
     * {@inheritdoc}
     */
    public function isFrontendRequest(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFrontendUrl(string $pathinfo): bool
    {
        return false;
    }
}
