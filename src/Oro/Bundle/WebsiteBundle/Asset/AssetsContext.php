<?php

namespace Oro\Bundle\WebsiteBundle\Asset;

use Symfony\Component\Asset\Context\RequestStackContext;

/**
 * Assets context with resolved base path for the current website.
 */
class AssetsContext extends RequestStackContext
{
    /**
     * @var BasePathResolver
     */
    protected $resolver;

    public function setBasePathResolver(BasePathResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    #[\Override]
    public function getBasePath(): string
    {
        return $this->resolver->resolveBasePath(parent::getBasePath());
    }
}
