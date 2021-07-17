<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\DraftBundle\EventListener\DraftableFilterListener as BaseDraftableFilterListener;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Prohibits to disable DraftableFilter on Storefront
 */
class DraftableFilterListener extends BaseDraftableFilterListener
{
    /**
     * @var FrontendHelper
     */
    private $frontendHelper;

    public function __construct(DoctrineHelper $doctrineHelper, FrontendHelper $frontendHelper)
    {
        $this->frontendHelper = $frontendHelper;
        parent::__construct($doctrineHelper);
    }

    public function onKernelController(FilterControllerEvent $event): void
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return;
        }

        parent::onKernelController($event);
    }
}
