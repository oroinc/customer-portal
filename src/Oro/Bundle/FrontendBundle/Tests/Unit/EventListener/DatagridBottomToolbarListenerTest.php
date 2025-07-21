<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FrontendBundle\EventListener\DatagridBottomToolbarListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatagridBottomToolbarListenerTest extends TestCase
{
    private DatagridConfiguration&MockObject $datagridConfig;
    private FrontendHelper&MockObject $frontendHelper;
    private BuildBefore&MockObject $event;
    private DatagridBottomToolbarListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->datagridConfig = $this->createMock(DatagridConfiguration::class);

        $this->event = $this->createMock(BuildBefore::class);
        $this->event->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->datagridConfig);

        $this->listener = new DatagridBottomToolbarListener($this->frontendHelper);
    }

    public function testIsNotApplicableNotFrontendRequest(): void
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(false);
        $this->datagridConfig->expects($this->never())
            ->method('offsetSetByPath');
        $this->listener->onBuildBefore($this->event);
    }

    public function testIsNotApplicableAlreadySet(): void
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->datagridConfig->expects($this->once())
            ->method('offsetGetByPath')
            ->with('[options][toolbarOptions][placement][bottom]')
            ->willReturn(false);
        $this->datagridConfig->expects($this->never())
            ->method('offsetSetByPath');
        $this->listener->onBuildBefore($this->event);
    }

    public function testOnBuildBefore(): void
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->datagridConfig->expects($this->once())
            ->method('offsetGetByPath')
            ->with('[options][toolbarOptions][placement][bottom]')
            ->willReturn(null);
        $this->datagridConfig->expects($this->once())
            ->method('offsetSetByPath')
            ->with('[options][toolbarOptions][placement][bottom]', true)
            ->willReturn(null);
        $this->listener->onBuildBefore($this->event);
    }
}
