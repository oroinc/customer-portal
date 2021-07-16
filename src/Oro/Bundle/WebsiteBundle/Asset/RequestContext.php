<?php

namespace Oro\Bundle\WebsiteBundle\Asset;

use Symfony\Component\Routing\RequestContext as BaseRequestContext;

/**
 * Request context with resolved base path for the current website.
 */
class RequestContext extends BaseRequestContext
{
    /**
     * @var BasePathResolver
     */
    protected $resolver;

    public function setBasePathResolver(BasePathResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->resolver->resolveBasePath(parent::getBaseUrl());
    }
}
