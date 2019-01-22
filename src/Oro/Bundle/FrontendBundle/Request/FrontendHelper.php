<?php

namespace Oro\Bundle\FrontendBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Helper class for check whether a request is a storefront or management console request.
 */
class FrontendHelper
{
    /** @var string */
    private $backendPrefix;

    /** @var RequestStack */
    private $requestStack;

    /**
     * @param string $backendPrefix
     * @param RequestStack $requestStack
     */
    public function __construct($backendPrefix, RequestStack $requestStack)
    {
        $this->backendPrefix = $backendPrefix;
        $this->requestStack = $requestStack;
    }

    /**
     * @param Request|null $request
     * @return bool
     */
    public function isFrontendRequest(Request $request = null)
    {
        if (null === $request) {
            $request = $this->requestStack->getCurrentRequest();
        }

        return null !== $request && $this->isFrontendUrl($request->getPathInfo());
    }

    /**
     * @param string $url
     * @return bool
     */
    public function isFrontendUrl($url)
    {
        // the least time consuming method to check whether URL is frontend
        return strpos($url, $this->backendPrefix) !== 0;
    }
}
