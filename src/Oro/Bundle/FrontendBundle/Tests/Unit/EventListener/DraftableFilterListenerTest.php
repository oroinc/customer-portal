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
use Symfony\Component\HttpKernel\HttpKernelInterface;

class DraftableFilterListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var DraftableFilterListener */
    private $listener;

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

        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            fn ($x) => $x,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onKernelController($event);
    }

    public function testOnKernelControllerNonFrontendRequest(): void
    {
        $request = Request::create('/entity/draftable/view/1', 'GET', ['id' => 1]);

        $this->mockEntityManagerWithDraftableFilter();

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            [new StubController(), 'viewAction'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

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
