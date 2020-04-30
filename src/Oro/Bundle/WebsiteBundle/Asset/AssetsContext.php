<?php

namespace Oro\Bundle\WebsiteBundle\Asset;

use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Assets context with resolved base path for the current website.
 */
class AssetsContext extends RequestStackContext
{
    /**
     * @var BasePathResolver
     */
    protected $resolver;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack);
    }

    /**
     * @param BasePathResolver $resolver
     */
    public function setBasePathResolver(BasePathResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath()
    {
        return $this->resolver->resolveBasePath(parent::getBasePath());
    }
}
