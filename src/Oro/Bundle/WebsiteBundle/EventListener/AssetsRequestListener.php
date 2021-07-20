<?php

namespace Oro\Bundle\WebsiteBundle\EventListener;

use Oro\Bundle\WebsiteBundle\Asset\RequestContext;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Fills the context of the assets request.
 */
class AssetsRequestListener
{
    /**
     * @var RequestContext
     */
    private $context;

    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->context->fromRequest($event->getRequest());
        $this->context->setParameter('_locale', $event->getRequest()->getLocale());
    }
}
