<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\SystemPageTargetBuilder;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Symfony\Component\Routing\RouterInterface;

class SystemPageTargetBuilderTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;

    /** @var RouterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $router;

    /** @var FeatureChecker|\PHPUnit\Framework\MockObject\MockObject */
    private $featureChecker;

    /** @var SystemPageTargetBuilder */
    private $builder;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->featureChecker = $this->createMock(FeatureChecker::class);

        $this->builder = new SystemPageTargetBuilder($this->router, $this->featureChecker);

        $this->setUpLoggerMock($this->builder);
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(false);
        $menuItem->expects($this->never())
            ->method('setUri');

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoSystemPageRoute(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('system_page_route')
            ->willReturn(null);
        $menuItem->expects($this->never())
            ->method('setUri');

        $this->builder->build($menuItem);
    }

    public function testBuildWhenRouteNotEnabled(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('system_page_route')
            ->willReturn($routeName = 'sample_route');

        $this->featureChecker->expects($this->once())
            ->method('isResourceEnabled')
            ->with($routeName, 'routes')
            ->willReturn(false);

        $menuItem->expects($this->never())
            ->method('setUri');

        $menuItem->expects($this->once())
            ->method('setDisplay')
            ->with(false);

        $this->builder->build($menuItem);
    }

    public function testBuildWhenCannotGenerateUrl(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('system_page_route')
            ->willReturn($routeName = 'sample_route');

        $this->featureChecker->expects($this->once())
            ->method('isResourceEnabled')
            ->with($routeName, 'routes')
            ->willReturn(true);

        $this->router->expects($this->once())
            ->method('generate')
            ->with($routeName)
            ->willThrowException(new \Exception());

        $this->assertLoggerWarningMethodCalled();

        $menuItem->expects($this->never())
            ->method('setUri');

        $menuItem->expects($this->once())
            ->method('setDisplay')
            ->with(false);

        $this->builder->build($menuItem);
    }

    public function testBuildWhenRouteEnabled(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('system_page_route')
            ->willReturn($routeName = 'sample_route');

        $this->featureChecker->expects($this->once())
            ->method('isResourceEnabled')
            ->with($routeName, 'routes')
            ->willReturn(true);

        $this->router->expects($this->once())
            ->method('generate')
            ->with($routeName)
            ->willReturn($url = 'sample/url');

        $menuItem->expects($this->once())
            ->method('setUri')
            ->with($url);

        $menuItem->expects($this->never())
            ->method('setDisplay');

        $this->builder->build($menuItem);
    }

    public function testBuildChildWhenRouteEnabled(): void
    {
        $parentMenuItem = $this->createMock(ItemInterface::class);
        $parentMenuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $parentMenuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([$menuItem = $this->createMock(ItemInterface::class)]);
        $parentMenuItem->expects($this->once())
            ->method('getExtra')
            ->with('system_page_route')
            ->willReturn(null);

        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('system_page_route')
            ->willReturn($routeName = 'sample_route');

        $this->featureChecker->expects($this->once())
            ->method('isResourceEnabled')
            ->with($routeName, 'routes')
            ->willReturn(true);

        $this->router->expects($this->once())
            ->method('generate')
            ->with($routeName)
            ->willReturn($url = 'sample/url');

        $menuItem->expects($this->once())
            ->method('setUri')
            ->with($url);

        $menuItem->expects($this->never())
            ->method('setDisplay');

        $this->builder->build($parentMenuItem);
    }
}
