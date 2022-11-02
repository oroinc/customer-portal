<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\EventListener\EnabledLocalizationsGridListener;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Component\Testing\Unit\EntityTrait;

class EnabledLocalizationsGridListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configManager;

    /**
     * @var EnabledLocalizationsGridListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->listener = new EnabledLocalizationsGridListener($this->configManager);
    }

    public function testOnBuildAfterWhenNoOrmDatasource(): void
    {
        $datasource = $this->createMock(DatasourceInterface::class);
        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects($this->once())
            ->method('getDatasource')
            ->willReturn($datasource);

        $this->configManager->expects($this->never())
            ->method('get');

        $event = new BuildAfter($datagrid);
        $this->listener->onBuildAfter($event);
    }

    public function testOnBuildAfterWithWebsiteId(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('ids', [1,2]);

        $datasource = $this->createMock(OrmDatasource::class);
        $datasource->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($queryBuilder);

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects($this->once())
            ->method('getDatasource')
            ->willReturn($datasource);

        $enabledLocalizationKey = Configuration::getConfigKeyByName(Configuration::ENABLED_LOCALIZATIONS);
        $this->configManager->expects($this->once())
            ->method('get')
            ->with($enabledLocalizationKey, false, false, null)
            ->willReturn([1,2]);

        $event = new BuildAfter($datagrid);
        $this->listener->onBuildAfter($event);
    }
}
