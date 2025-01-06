<?php

namespace Oro\Bundle\WebsiteBundle\Asset;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Should be used to resolve assets base path for website with sub folder.
 */
class BasePathResolver
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function resolveBasePath(string $defaultBasePath): string
    {
        $mainRequest = $this->requestStack->getMainRequest();
        if (null === $mainRequest) {
            return $defaultBasePath;
        }

        $configuredPath = $mainRequest->server->get('WEBSITE_PATH');
        if (!$configuredPath) {
            return $defaultBasePath;
        }

        return str_replace($configuredPath, '', $defaultBasePath);
    }
}
