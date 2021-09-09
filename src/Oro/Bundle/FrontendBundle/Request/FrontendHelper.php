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
     * Used if the check should return that a request is the storefront or management console
     * without any additional checks
     * @var bool
     */
    private $emulateFrontendRequest;

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
     */
    public function isFrontendRequest(): bool
    {
        if (null !== $this->emulateFrontendRequest) {
            return $this->emulateFrontendRequest;
        }

        $request = $this->requestStack->getMainRequest();

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
        if (str_starts_with($pathinfo, $this->backendPrefix)) {
            $prefixLength = \strlen($this->backendPrefix);

            return
                $prefixLength !== \strlen($pathinfo)
                && '/' !== $pathinfo[$prefixLength];
        }

        return true;
    }

    /**
     * Switches the {@see isFrontendRequest} check to return that a request is the storefront request
     * without additional checks.
     */
    public function emulateFrontendRequest(): void
    {
        $this->emulateFrontendRequest = true;
    }

    /**
     * Switches the {@see isFrontendRequest} check to return that a request is management console request
     * without additional checks.
     */
    public function emulateBackendRequest(): void
    {
        $this->emulateFrontendRequest = false;
    }

    /**
     * Removes the {@see isFrontendRequest} check emulation.
     */
    public function resetRequestEmulation(): void
    {
        $this->emulateFrontendRequest = null;
    }
}
