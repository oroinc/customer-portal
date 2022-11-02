<?php

namespace Oro\Bundle\FrontendBundle\Placeholder;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * Helper class that can be used in placeholder configuration files (placeholders.yml)
 * to check whether a request is a storefront or management console request.
 */
class FrontendFilter
{
    /** @var FrontendHelper */
    private $helper;

    public function __construct(FrontendHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return bool
     */
    public function isFrontendRoute()
    {
        return $this->helper->isFrontendRequest();
    }

    /**
     * @return bool
     */
    public function isBackendRoute()
    {
        return !$this->helper->isFrontendRequest();
    }
}
