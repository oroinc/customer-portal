<?php

namespace Oro\Bundle\WebsiteBundle\Asset;

use Symfony\Component\Routing\RequestContext as BaseRequestContext;

/**
 * Request context with resolved base path for the current website.
 */
class RequestContext extends BaseRequestContext
{
    protected ?BasePathResolver $resolver = null;

    public function setBasePathResolver(BasePathResolver $resolver): void
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl(): string
    {
        return $this->resolver ? $this->resolver->resolveBasePath(parent::getBaseUrl()) : parent::getBaseUrl();
    }

    /**
     * Mimics the logic of parent's ::fromUri().
     *
     * @param string $uri
     * @param string $host
     * @param string $scheme
     * @param int $httpPort
     * @param int $httpsPort
     * @return self
     */
    public static function fromUri(
        string $uri,
        string $host = 'localhost',
        string $scheme = 'http',
        int $httpPort = 80,
        int $httpsPort = 443
    ): self {
        $parsedUri = parse_url($uri);
        $scheme = $parsedUri['scheme'] ?? $scheme;
        $host = $parsedUri['host'] ?? $host;

        if (isset($parsedUri['port'])) {
            if ('http' === $scheme) {
                $httpPort = $parsedUri['port'];
            } elseif ('https' === $scheme) {
                $httpsPort = $parsedUri['port'];
            }
        }

        return new self($parsedUri['path'] ?? '', 'GET', $host, $scheme, $httpPort, $httpsPort);
    }
}
