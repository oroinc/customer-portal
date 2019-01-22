<?php

namespace Oro\Bundle\FrontendBundle\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * This class is used to substitute FrontendHelper during installation of the application.
 * It is supposed that all requests are management console requests until the installation is finished.
 * @see \Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension::configureFrontendHelper
 */
class NotInstalledFrontendHelper extends FrontendHelper
{
    /**
     * {@inheritdoc}
     */
    public function isFrontendRequest(Request $request = null)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFrontendUrl($url)
    {
        return false;
    }
}
