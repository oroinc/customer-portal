<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityExtendBundle\Grid\AdditionalFieldsExtension;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * Disables custom fields for storefront grids.
 */
class DatagridFieldsListener
{
    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(FrontendHelper $frontendHelper)
    {
        $this->frontendHelper = $frontendHelper;
    }

    public function onBuildBefore(BuildBefore $event)
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return;
        }

        $config = $event->getConfig();
        $config->offsetSetByPath(AdditionalFieldsExtension::ADDITIONAL_FIELDS_CONFIG_PATH, []);
        $config->setExtendedEntityClassName(null);
    }
}
