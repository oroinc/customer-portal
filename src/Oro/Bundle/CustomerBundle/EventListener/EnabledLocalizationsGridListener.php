<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;

/**
 * This listener provides enabled localizations ids to the grid.
 */
class EnabledLocalizationsGridListener
{
    private ConfigManager $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function onBuildAfter(BuildAfter $event): void
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $datasource->getQueryBuilder()->setParameter('ids', $this->getEnabledLocalizations($event));
        }
    }

    private function getEnabledLocalizations(BuildAfter $event): array
    {
        $key = Configuration::getConfigKeyByName(Configuration::ENABLED_LOCALIZATIONS);

        return $this->configManager->get($key);
    }
}
