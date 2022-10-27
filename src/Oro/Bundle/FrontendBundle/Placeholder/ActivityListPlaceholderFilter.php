<?php

namespace Oro\Bundle\FrontendBundle\Placeholder;

use Oro\Bundle\ActivityListBundle\Placeholder\PlaceholderFilter;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UIBundle\Event\BeforeGroupingChainWidgetEvent;

/**
 * Helper class that can be used in placeholder configuration files (placeholders.yml)
 * to check whether an entity can have activities.
 */
class ActivityListPlaceholderFilter
{
    /** @var PlaceholderFilter */
    private $filter;

    /** @var FrontendHelper */
    private $helper;

    public function __construct(PlaceholderFilter $filter, FrontendHelper $helper)
    {
        $this->filter = $filter;
        $this->helper = $helper;
    }

    /**
     * @param object|null $entity
     * @param int|null $pageType
     * @return bool
     */
    public function isApplicable($entity = null, $pageType = null)
    {
        return
            !$this->helper->isFrontendRequest()
            && $this->filter->isApplicable($entity, $pageType);
    }

    public function isAllowedButton(BeforeGroupingChainWidgetEvent $event)
    {
        if ($this->helper->isFrontendRequest()) {
            // Clear allowed widgets
            $event->setWidgets([]);
            $event->stopPropagation();
        } else {
            $this->filter->isAllowedButton($event);
        }
    }
}
