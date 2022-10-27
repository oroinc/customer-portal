<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * Makes the bottom toolbar for storefront grids enabled by default.
 */
class DatagridBottomToolbarListener
{
    private const TOOLBAR_BOTTOM_PLACEMENT = '[options][toolbarOptions][placement][bottom]';

    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(FrontendHelper $frontendHelper)
    {
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function onBuildBefore(BuildBefore $event)
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return;
        }

        $config = $event->getConfig();
        if (null === $config->offsetGetByPath(self::TOOLBAR_BOTTOM_PLACEMENT)) {
            $config->offsetSetByPath(self::TOOLBAR_BOTTOM_PLACEMENT, true);
        }
    }
}
