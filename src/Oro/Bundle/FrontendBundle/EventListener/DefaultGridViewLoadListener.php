<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\GridViewsLoadEvent;
use Oro\Bundle\DataGridBundle\EventListener\DefaultGridViewLoadListener as BaseDefaultGridViewLoadListener;
use Oro\Bundle\FrontendBundle\Datagrid\Extension\FrontendDatagridExtension;

/**
 * Sets label for default All grid view for frontend datagrids.
 */
class DefaultGridViewLoadListener extends BaseDefaultGridViewLoadListener
{
    public function onViewsLoad(GridViewsLoadEvent $event): void
    {
        if ($this->isFrontendDatagrid($event->getGridConfiguration())) {
            parent::onViewsLoad($event);
        }
    }

    private function isFrontendDatagrid(DatagridConfiguration $datagridConfiguration): bool
    {
        return (bool)$datagridConfiguration->offsetGetByPath(FrontendDatagridExtension::FRONTEND_OPTION_PATH, false);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllGridViewTranslationKey(string $className): string
    {
        $provider = $this->configManager->getProvider('entity');

        return $provider ? (string) $provider->getConfig($className)->get('frontend_grid_all_view_label') : '';
    }
}
