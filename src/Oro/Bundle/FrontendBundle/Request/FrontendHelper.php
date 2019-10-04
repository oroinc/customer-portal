<?php

namespace Oro\Bundle\FrontendBundle\Request;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The helper class to check whether the current request is the storefront or management console request.
 */
class FrontendHelper
{
    /** @var string */
    private $backendPrefix;

    /** @var RequestStack */
    private $requestStack;

    /**
     * @param string       $backendPrefix
     * @param RequestStack $requestStack
     */
    public function __construct($backendPrefix, RequestStack $requestStack)
    {
        $this->backendPrefix = $backendPrefix;
        $this->requestStack = $requestStack;
    }

    /**
     * Checks whether the current HTTP request is the storefront or management console request.
     *
     * @return bool
     */
    public function isFrontendRequest(): bool
    {
        $request = $this->requestStack->getMasterRequest();

        return
            null !== $request
            && $this->isFrontendUrl($request->getPathInfo());
    }

    /**
     * Checks whether the given URL is the storefront or management console URL.
     *
     * @param string $pathinfo The path info to be checked (raw format, i.e. not urldecoded)
     *                         {@see \Symfony\Component\HttpFoundation\Request::getPathInfo}
     *
     * @return bool
     */
    public function isFrontendUrl(string $pathinfo): bool
    {
        // the least time consuming method to check whether URL is frontend
        if (strpos($pathinfo, $this->backendPrefix) === 0) {
            $prefixLength = \strlen($this->backendPrefix);

            return
                $prefixLength !== \strlen($pathinfo)
                && '/' !== $pathinfo[$prefixLength];
        }

        return true;
    }
}
