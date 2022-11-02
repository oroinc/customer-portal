<?php

namespace Oro\Bundle\WebsiteBundle\Asset;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Should be used to resolve assets base path for website with sub folder.
 */
class BasePathResolver
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function resolveBasePath(string $defaultBasePath): string
    {
        $masterRequest = $this->requestStack->getMainRequest();
        if ($masterRequest && $configuredPath = $masterRequest->server->get('WEBSITE_PATH')) {
            return str_replace($configuredPath, '', $defaultBasePath);
        }

        return $defaultBasePath;
    }
}
