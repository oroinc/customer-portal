<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Layout;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Layout\MenuItemRenderer;
use Oro\Bundle\LayoutBundle\Layout\LayoutManager;
use Oro\Bundle\NavigationBundle\Tests\Unit\Entity\Stub\MenuItemStub;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Component\Layout\Layout;
use Oro\Component\Layout\LayoutBuilderInterface;
use Oro\Component\Layout\LayoutContext;
use Oro\Component\Layout\LayoutFactoryInterface;

class MenuItemRendererTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;

    private LayoutManager|\PHPUnit\Framework\MockObject\MockObject $layoutManager;

    private LayoutBuilderInterface|\PHPUnit\Framework\MockObject\MockObject $layoutBuilder;

    private MenuItemRenderer $menuItemRenderer;

    protected function setUp(): void
    {
        $this->layoutManager = $this->createMock(LayoutManager::class);

        $layoutFactory = $this->createMock(LayoutFactoryInterface::class);
        $this->layoutManager
            ->expects(self::any())
            ->method('getLayoutFactory')
            ->willReturn($layoutFactory);

        $this->layoutBuilder = $this->createMock(LayoutBuilderInterface::class);
        $layoutFactory
            ->expects(self::any())
            ->method('createLayoutBuilder')
            ->willReturn($this->layoutBuilder);

        $this->menuItemRenderer = new MenuItemRenderer($this->layoutManager);
        $this->setUpLoggerMock($this->menuItemRenderer);
    }

    public function testRenderWhenExceptionDuringRendering(): void
    {
        $exception = new \Exception('some error');
        $menuItem = new MenuItemStub();
        $menuItem->setName('sample_name');
        $layoutContext = new LayoutContext(
            [
                'data' => ['menu_item' => $menuItem],
                'menu_template' => (string)$menuItem->getExtra(MenuUpdate::MENU_TEMPLATE),
                'menu_name' => $menuItem->getName(),
            ],
            ['menu_template', 'menu_name']
        );

        $layout = $this->createMock(Layout::class);
        $this->layoutBuilder
            ->expects(self::once())
            ->method('add')
            ->with('menu_item_root', null, 'container');

        $this->layoutBuilder
            ->expects(self::once())
            ->method('getLayout')
            ->with($layoutContext)
            ->willReturn($layout);

        $layout
            ->expects(self::once())
            ->method('render')
            ->willThrowException($exception);

        $this->loggerMock
            ->expects(self::once())
            ->method('error')
            ->with(
                'Error occurred while rendering menu item "{menu_item_name}".',
                ['throwable' => $exception, 'menu_item_name' => $menuItem->getName(), 'menu_item' => $menuItem]
            );

        self::assertSame('', $this->menuItemRenderer->render($menuItem));
    }

    public function testRender(): void
    {
        $menuItem = new MenuItemStub();
        $menuItem->setName('sample_name');
        $layoutContext = new LayoutContext(
            [
                'data' => ['menu_item' => $menuItem],
                'menu_template' => (string)$menuItem->getExtra(MenuUpdate::MENU_TEMPLATE),
                'menu_name' => $menuItem->getName(),
            ],
            ['menu_template', 'menu_name']
        );

        $layout = $this->createMock(Layout::class);
        $this->layoutBuilder
            ->expects(self::once())
            ->method('add')
            ->with('menu_item_root', null, 'container');
        
        $this->layoutBuilder
            ->expects(self::once())
            ->method('getLayout')
            ->with($layoutContext)
            ->willReturn($layout);

        $result = 'sample result';
        $layout
            ->expects(self::once())
            ->method('render')
            ->willReturn($result);

        $this->loggerMock
            ->expects(self::never())
            ->method('error');

        self::assertEquals($result, $this->menuItemRenderer->render($menuItem));
    }
}
