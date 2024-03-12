<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Consumption\Extension;

use Oro\Bundle\FrontendBundle\Consumption\Extension\ThemeTransferConsumptionExtension;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\MessageQueue\Consumption\Context;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class ThemeTransferConsumptionExtensionTest extends TestCase
{
    private CurrentThemeProvider|MockObject $currentThemeProvider;
    private ThemeTransferConsumptionExtension $themeTransferConsumptionExtension;

    protected function setUp(): void
    {
        $this->currentThemeProvider = $this->createMock(CurrentThemeProvider::class);
        $this->themeTransferConsumptionExtension = new ThemeTransferConsumptionExtension($this->currentThemeProvider);
    }

    public function testOnPreReceivedWithoutThemeId(): void
    {
        $context = $this->createMock(Context::class);
        $context
            ->method('getMessage')
            ->willReturn($this->createMock(MessageInterface::class));

        $this->currentThemeProvider
            ->expects($this->never())
            ->method('getCurrentRequest');

        $this->themeTransferConsumptionExtension->onPreReceived($context);
    }

    public function testOnPreReceivedWithThemeIdAndCurrentRequest(): void
    {
        $context = $this->prepareContext();

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes
            ->expects(self::once())
            ->method('set')
            ->with('_theme', 'testTheme');
        $this->currentThemeProvider
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->themeTransferConsumptionExtension->onPreReceived($context);
    }

    public function testOnPreReceivedWithThemeIdAndNoCurrentRequest(): void
    {
        $context = $this->prepareContext();

        $this->currentThemeProvider
            ->method('getCurrentRequest')
            ->willReturn(null);
        $called = false;
        $this->currentThemeProvider
            ->expects(self::once())
            ->method('setCurrentRequest')
            ->willReturnCallback(
                function (Request $request) use (&$called) {
                    $called = true;
                    $this->assertEquals('testTheme', $request->attributes->get('_theme'));
                }
            );

        $this->themeTransferConsumptionExtension->onPreReceived($context);
        $this->assertTrue($called);
    }

    public function testOnPostReceivedWithoutCurrentProviderThemeId(): void
    {
        $context = $this->createMock(Context::class);

        $this->currentThemeProvider
            ->expects($this->never())
            ->method('setCurrentRequest');

        $this->themeTransferConsumptionExtension->onPostReceived($context);
    }

    public function testOriginalThemeRestoredToOriginalRequest(): void
    {
        $context = $this->prepareContext();

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes
            ->expects(self::once())
            ->method('get')
            ->willReturn('originalThemeId');
        $this->currentThemeProvider
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->themeTransferConsumptionExtension->onPreReceived($context);

        $request->attributes
            ->expects(self::once())
            ->method('set')
            ->with('_theme', 'originalThemeId');

        $this->themeTransferConsumptionExtension->onPostReceived($context);
    }

    public function testEmptyRequestIsRestored(): void
    {
        $context = $this->prepareContext();

        $this->currentThemeProvider
            ->method('getCurrentRequest')
            ->willReturn(null);
        $callNr = 0;
        $this->currentThemeProvider
            ->expects(self::exactly(2))
            ->method('setCurrentRequest')
            ->willReturnCallback(
                function (?Request $request) use (&$callNr) {
                    $callNr++;
                    if ($callNr === 1) {
                        $this->assertInstanceOf(Request::class, $request);
                    } else {
                        $this->assertNull($request);
                    }
                }
            );
        $this->themeTransferConsumptionExtension->onPreReceived($context);
        $this->themeTransferConsumptionExtension->onPostReceived($context);
        $this->assertEquals(2, $callNr, 'SetCurrentRequest should be called twice');
    }

    private function prepareContext(): Context|MockObject
    {
        $context = $this->createMock(Context::class);
        $message = $this->createMock(MessageInterface::class);
        $message
            ->method('getProperty')
            ->willReturn('testTheme');
        $context
            ->method('getMessage')
            ->willReturn($message);

        return $context;
    }
}
