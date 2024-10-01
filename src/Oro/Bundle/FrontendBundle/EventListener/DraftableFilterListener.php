<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\DraftBundle\EventListener\DraftableFilterListener as BaseDraftableFilterListener;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Prohibits to disable DraftableFilter on Storefront
 */
class DraftableFilterListener extends BaseDraftableFilterListener
{
    private FrontendHelper $frontendHelper;

    public function __construct(DoctrineHelper $doctrineHelper, FrontendHelper $frontendHelper)
    {
        $this->frontendHelper = $frontendHelper;
        parent::__construct($doctrineHelper);
    }

    #[\Override]
    public function onKernelController(ControllerEvent $event): void
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return;
        }

        parent::onKernelController($event);
    }

    #[\Override]
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return;
        }

        parent::onKernelRequest($event);
    }
}
