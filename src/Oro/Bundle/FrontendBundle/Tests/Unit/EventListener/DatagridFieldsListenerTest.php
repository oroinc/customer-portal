<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityExtendBundle\Grid\AdditionalFieldsExtension;
use Oro\Bundle\EntityExtendBundle\Grid\DynamicFieldsExtension;
use Oro\Bundle\FrontendBundle\EventListener\DatagridFieldsListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

class DatagridFieldsListenerTest extends FrontendDatagridListenerTestCase
{
    /**
     * @var DatagridFieldsListener
     */
    protected $listener;

    /**
     * @var BuildBefore|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $event;

    public function setUp()
    {
        parent::setUp();
        $this->event = $this->getBuildBeforeEventMock($this->datagridConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function createListener(FrontendHelper $helper)
    {
        return new DatagridFieldsListener($helper);
    }

    public function testIsNotApplicable()
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(false);
        $this->datagridConfig->expects($this->never())
            ->method('offsetSetByPath');
        $this->listener->onBuildBefore($this->event);
    }

    public function testOnBuildBefore()
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->datagridConfig->expects($this->once())
            ->method('offsetSetByPath')
            ->with(AdditionalFieldsExtension::ADDITIONAL_FIELDS_CONFIG_PATH, [])
            ->willReturn(null);
        $this->datagridConfig->expects($this->once())
            ->method('setExtendedEntityClassName')
            ->with(null);
        $this->listener->onBuildBefore($this->event);
    }
}
