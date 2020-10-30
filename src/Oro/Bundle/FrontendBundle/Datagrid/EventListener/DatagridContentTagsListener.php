<?php

namespace Oro\Bundle\FrontendBundle\Datagrid\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SyncBundle\Content\DataGridTagListener;

/**
 * Disables datagrid content tags for store front because websockets are not implemented there.
 */
class DatagridContentTagsListener
{
    /** @var DataGridTagListener */
    private $dataGridTagListener;

    /** @var FrontendHelper */
    private $frontendHelper;

    /**
     * @param DataGridTagListener $dataGridTagListener
     * @param FrontendHelper $frontendHelper
     */
    public function __construct(DataGridTagListener $dataGridTagListener, FrontendHelper $frontendHelper)
    {
        $this->dataGridTagListener = $dataGridTagListener;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * @param BuildAfter $event
     */
    public function buildAfter(BuildAfter $event): void
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return;
        }

        $this->dataGridTagListener->buildAfter($event);
    }
}
