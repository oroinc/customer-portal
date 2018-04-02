<?php

namespace Oro\Bundle\FrontendBundle\Placeholder;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpFoundation\RequestStack;

class FrontendFilter
{
    /**
     * @var FrontendHelper
     */
    protected $helper;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param FrontendHelper $helper
     * @param RequestStack $requestStack
     */
    public function __construct(FrontendHelper $helper, RequestStack $requestStack)
    {
        $this->helper = $helper;
        $this->requestStack = $requestStack;
    }

    /**
     * @return bool
     */
    public function isFrontendRoute()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        return $this->helper->isFrontendRequest($request);
    }

    /**
     * @return bool
     */
    public function isBackendRoute()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return true;
        }

        return !$this->helper->isFrontendRequest($request);
    }
}
