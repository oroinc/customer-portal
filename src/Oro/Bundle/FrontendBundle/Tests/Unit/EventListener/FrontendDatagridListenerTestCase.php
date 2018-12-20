<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FrontendBundle\EventListener\AbstractFrontendDatagridListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

abstract class FrontendDatagridListenerTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AbstractFrontendDatagridListener
     */
    protected $listener;

    /**
     * @var DatagridConfiguration|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $datagridConfig;

    /**
     * @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $frontendHelper;

    public function setUp()
    {
        $this->frontendHelper = $this->getMockBuilder('Oro\Bundle\FrontendBundle\Request\FrontendHelper')
            ->disableOriginalConstructor()->getMock();
        $this->datagridConfig = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()->getMock();
        $this->listener = $this->createListener($this->frontendHelper);
    }

    /**
     * @param FrontendHelper $frontendHelper
     * @return mixed
     */
    abstract protected function createListener(FrontendHelper $frontendHelper);

    /**
     * @param DatagridConfiguration $datagridConfig
     * @return BuildBefore|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getBuildBeforeEventMock(DatagridConfiguration $datagridConfig)
    {
        $event = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Event\BuildBefore')
            ->disableOriginalConstructor()->getMock();

        $event->expects($this->once())
            ->method('getConfig')
            ->willReturn($datagridConfig);

        return $event;
    }
}
