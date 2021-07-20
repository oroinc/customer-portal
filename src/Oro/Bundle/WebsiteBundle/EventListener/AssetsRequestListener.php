<?php

namespace Oro\Bundle\WebsiteBundle\EventListener;

use Oro\Bundle\WebsiteBundle\Asset\RequestContext;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Fills the context of the assets request.
 */
class AssetsRequestListener
{
    private RequestContext $context;

    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->context->fromRequest($event->getRequest());
        $this->context->setParameter('_locale', $event->getRequest()->getLocale());
    }
}
