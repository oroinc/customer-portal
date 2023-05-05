<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Layout;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Layout\MenuItemRenderer;
use Oro\Bundle\LayoutBundle\Layout\LayoutManager;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Component\Layout\Layout;
use Oro\Component\Layout\LayoutBuilderInterface;
use Oro\Component\Layout\LayoutContext;
use Oro\Component\Layout\LayoutFactoryInterface;

class MenuItemRendererTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;
    use MenuItemTestTrait;

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

        $this->menuItemRenderer = $this->getRenderer(false);
        $this->setUpLoggerMock($this->menuItemRenderer);
    }

    private function getRenderer(bool $debug): MenuItemRenderer
    {
        return new MenuItemRenderer($this->layoutManager, $debug);
    }

    /**
     * @dataProvider renderWhenExceptionDuringRenderingDataProvider
     */
    public function testRenderWhenExceptionDuringRendering(bool $debug, string $expected): void
    {
        $exception = new \Exception('some error');
        $menuItem = $this->createItem('sample_item');
        $layoutContext = new LayoutContext(
            [
                'data' => ['menu_item' => $menuItem],
                'menu_template' => (string)$menuItem->getExtra(MenuUpdate::MENU_TEMPLATE),
                'menu_item_name' => $menuItem->getName(),
            ],
            ['menu_template', 'menu_item_name']
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

        $renderer = $this->getRenderer($debug);
        $renderer->setLogger($this->loggerMock);

        $this->loggerMock
            ->expects(self::once())
            ->method('error')
            ->with(
                'Error occurred while rendering menu item "{menu_item_name}": {error}',
                [
                    'throwable' => $exception,
                    'error' => $exception->getMessage(),
                    'menu_item_name' => $menuItem->getName(),
                    'menu_item' => $menuItem,
                ]
            );

        self::assertSame($expected, $renderer->render($menuItem));
    }

    public function renderWhenExceptionDuringRenderingDataProvider(): array
    {
        return [
            [false, ''],
            [
                true,
                '<div class="alert alert-error alert--compact" role="alert">' . PHP_EOL
                . '    <span class="fa-exclamation alert-icon" aria-hidden="true"></span>' . PHP_EOL
                . '    Rendering of the menu item "sample_item" failed: some error' . PHP_EOL
                . '</div>',
            ],
        ];
    }

    public function testRender(): void
    {
        $menuItem = $this->createItem('sample_item');
        $layoutContext = new LayoutContext(
            [
                'data' => ['menu_item' => $menuItem],
                'menu_template' => (string)$menuItem->getExtra(MenuUpdate::MENU_TEMPLATE),
                'menu_item_name' => $menuItem->getName(),
            ],
            ['menu_template', 'menu_item_name']
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
