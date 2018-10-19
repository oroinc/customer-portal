<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Placeholder;

use Oro\Bundle\ActivityListBundle\Placeholder\PlaceholderFilter;
use Oro\Bundle\FrontendBundle\Placeholder\ActivityListPlaceholderFilter;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UIBundle\Event\BeforeGroupingChainWidgetEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityListPlaceholderFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|PlaceholderFilter
     */
    protected $basicFilter;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FrontendHelper
     */
    protected $helper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RequestStack
     */
    protected $requestStack;

    /**
     * @var ActivityListPlaceholderFilter
     */
    protected $filter;

    protected function setUp()
    {
        $this->basicFilter = $this->getMockBuilder('Oro\Bundle\ActivityListBundle\Placeholder\PlaceholderFilter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = $this->getMockBuilder('Oro\Bundle\FrontendBundle\Request\FrontendHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filter = new ActivityListPlaceholderFilter($this->basicFilter, $this->helper, $this->requestStack);
    }

    public function testIsApplicableNoRequest()
    {
        $entity = new \stdClass();
        $pageType = 'view';

        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue(null));

        $this->basicFilter->expects($this->once())
            ->method('isApplicable')
            ->with($entity, $pageType)
            ->will($this->returnValue(true));

        $this->assertTrue($this->filter->isApplicable($entity, $pageType));
    }

    public function testIsApplicableNotFrontend()
    {
        $entity = new \stdClass();
        $pageType = 1;

        $this->assertIsFrontendRouteCall(false);

        $this->basicFilter->expects($this->once())
            ->method('isApplicable')
            ->with($entity, $pageType)
            ->will($this->returnValue(true));

        $this->assertTrue($this->filter->isApplicable($entity, $pageType));
    }

    public function testIsApplicable()
    {
        $entity = new \stdClass();
        $pageType = 1;

        $this->assertIsFrontendRouteCall(true);

        $this->basicFilter->expects($this->never())
            ->method('isApplicable');

        $this->assertFalse($this->filter->isApplicable($entity, $pageType));
    }

    public function testIsAllowedButtonNotFrontend()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|BeforeGroupingChainWidgetEvent $event */
        $event = $this->getMockBuilder('Oro\Bundle\UIBundle\Event\BeforeGroupingChainWidgetEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertIsFrontendRouteCall(false);

        $this->basicFilter->expects($this->once())
            ->method('isAllowedButton')
            ->with($event);

        $this->filter->isAllowedButton($event);
    }

    public function testIsAllowedButton()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|BeforeGroupingChainWidgetEvent $event */
        $event = $this->getMockBuilder('Oro\Bundle\UIBundle\Event\BeforeGroupingChainWidgetEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertIsFrontendRouteCall(true);

        $event->expects($this->once())
            ->method('setWidgets')
            ->with([]);
        $event->expects($this->once())
            ->method('stopPropagation');

        $this->basicFilter->expects($this->never())
            ->method('isAllowedButton');

        $this->filter->isAllowedButton($event);
    }

    /**
     * @param bool $isFrontend
     */
    protected function assertIsFrontendRouteCall($isFrontend)
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $this->helper->expects($this->once())
            ->method('isFrontendRequest')
            ->with($request)
            ->will($this->returnValue($isFrontend));
    }
}
