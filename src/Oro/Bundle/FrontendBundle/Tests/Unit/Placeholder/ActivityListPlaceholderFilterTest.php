<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Placeholder;

use Oro\Bundle\ActivityListBundle\Placeholder\PlaceholderFilter;
use Oro\Bundle\FrontendBundle\Placeholder\ActivityListPlaceholderFilter;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UIBundle\Event\BeforeGroupingChainWidgetEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityListPlaceholderFilterTest extends \PHPUnit\Framework\TestCase
{
    private const BACKEND_PREFIX = '/admin';

    /** @var \PHPUnit\Framework\MockObject\MockObject|PlaceholderFilter */
    private $basicFilter;

    protected function setUp(): void
    {
        $this->basicFilter = $this->createMock(PlaceholderFilter::class);
    }

    private function getFilter(Request $currentRequest = null): ActivityListPlaceholderFilter
    {
        $requestStack = new RequestStack();
        if (null !== $currentRequest) {
            $requestStack->push($currentRequest);
        }

        return new ActivityListPlaceholderFilter(
            $this->basicFilter,
            new FrontendHelper(self::BACKEND_PREFIX, $requestStack)
        );
    }

    public function testIsApplicableNoRequest()
    {
        $entity = new \stdClass();
        $pageType = 'view';

        $this->basicFilter->expects($this->once())
            ->method('isApplicable')
            ->with($entity, $pageType)
            ->will($this->returnValue(true));

        $filter = $this->getFilter();
        $this->assertTrue($filter->isApplicable($entity, $pageType));
    }

    public function testIsApplicableNotFrontend()
    {
        $entity = new \stdClass();
        $pageType = 1;

        $this->basicFilter->expects($this->once())
            ->method('isApplicable')
            ->with($entity, $pageType)
            ->will($this->returnValue(true));

        $filter = $this->getFilter(Request::create(self::BACKEND_PREFIX . '/backend'));
        $this->assertTrue($filter->isApplicable($entity, $pageType));
    }

    public function testIsApplicable()
    {
        $entity = new \stdClass();
        $pageType = 1;

        $this->basicFilter->expects($this->never())
            ->method('isApplicable');

        $filter = $this->getFilter(Request::create('/frontend'));
        $this->assertFalse($filter->isApplicable($entity, $pageType));
    }

    public function testIsAllowedButtonNotFrontend()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|BeforeGroupingChainWidgetEvent $event */
        $event = $this->createMock(BeforeGroupingChainWidgetEvent::class);

        $this->basicFilter->expects($this->once())
            ->method('isAllowedButton')
            ->with($event);

        $filter = $this->getFilter(Request::create(self::BACKEND_PREFIX . '/backend'));
        $filter->isAllowedButton($event);
    }

    public function testIsAllowedButton()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|BeforeGroupingChainWidgetEvent $event */
        $event = $this->createMock(BeforeGroupingChainWidgetEvent::class);

        $event->expects($this->once())
            ->method('setWidgets')
            ->with([]);
        $event->expects($this->once())
            ->method('stopPropagation');

        $this->basicFilter->expects($this->never())
            ->method('isAllowedButton');

        $filter = $this->getFilter(Request::create('/frontend'));
        $filter->isAllowedButton($event);
    }
}
