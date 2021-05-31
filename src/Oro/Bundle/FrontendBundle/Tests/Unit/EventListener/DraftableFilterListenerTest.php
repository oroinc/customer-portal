<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\FilterCollection;
use Oro\Bundle\DraftBundle\Doctrine\DraftableFilter;
use Oro\Bundle\DraftBundle\Tests\Unit\Stub\DraftableEntityStub;
use Oro\Bundle\DraftBundle\Tests\Unit\Stub\StubController;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FrontendBundle\EventListener\DraftableFilterListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class DraftableFilterListenerTest extends \PHPUnit\Framework\TestCase
{
    private DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper;

    private FrontendHelper|\PHPUnit\Framework\MockObject\MockObject $frontendHelper;

    private DraftableFilterListener $listener;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->listener = new DraftableFilterListener(
            $this->doctrineHelper,
            $this->frontendHelper
        );
    }

    public function testOnKernelControllerFrontendRequest(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->doctrineHelper->expects(self::never())
            ->method('getEntityManagerForClass');

        $event = $this->createMock(ControllerEvent::class);

        $this->listener->onKernelController($event);
    }

    public function testOnKernelControllerNonFrontendRequest(): void
    {
        $request = Request::create('/entity/draftable/view/1', 'GET', ['id' => 1]);

        $this->mockEntityManagerWithDraftableFilter();

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $event = $this->createMock(ControllerEvent::class);
        $event->expects(self::any())
            ->method('getRequest')
            ->willReturn($request);
        $event->expects(self::any())
            ->method('getController')
            ->willReturn([new StubController(), 'viewAction']);

        $this->listener->onKernelController($event);
    }

    private function mockEntityManagerWithDraftableFilter(): void
    {
        $filters = $this->createMock(FilterCollection::class);
        $filters->expects(self::once())
            ->method('isEnabled')
            ->with(DraftableFilter::FILTER_ID)
            ->willReturn(false);

        $em = $this->createMock(EntityManager::class);
        $em->expects(self::once())
            ->method('getFilters')
            ->willReturn($filters);

        $this->doctrineHelper->expects(self::once())
            ->method('getEntityManagerForClass')
            ->with(DraftableEntityStub::class)
            ->willReturn($em);
    }
}
