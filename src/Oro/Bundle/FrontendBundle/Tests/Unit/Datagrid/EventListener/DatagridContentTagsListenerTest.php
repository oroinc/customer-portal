<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Datagrid\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\FrontendBundle\Datagrid\EventListener\DatagridContentTagsListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SyncBundle\Content\DataGridTagListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatagridContentTagsListenerTest extends TestCase
{
    private DataGridTagListener&MockObject $dataGridTagListener;
    private FrontendHelper&MockObject $frontendHelper;
    private DatagridContentTagsListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->dataGridTagListener = $this->createMock(DataGridTagListener::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->listener = new DatagridContentTagsListener($this->dataGridTagListener, $this->frontendHelper);
    }

    public function testBuildAfterWhenFrontend(): void
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->dataGridTagListener->expects($this->never())
            ->method($this->anything());

        $this->listener->buildAfter($this->createMock(BuildAfter::class));
    }

    public function testBuildAfterWhenNotFrontend(): void
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $event = $this->createMock(BuildAfter::class);
        $this->dataGridTagListener->expects($this->once())
            ->method('buildAfter')
            ->with($event);

        $this->listener->buildAfter($event);
    }
}
