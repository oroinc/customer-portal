<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Job;

use Oro\Bundle\FrontendBundle\Job\ThemeTransferJobMiddleware;
use Oro\Component\Layout\Exception\NotRequestContextRuntimeException;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\MessageQueue\Client\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemeTransferJobMiddlewareTest extends TestCase
{
    private CurrentThemeProvider|MockObject $currentThemeProvider;
    private ThemeTransferJobMiddleware $themeTransferJobMiddleware;

    #[\Override]
    protected function setUp(): void
    {
        $this->currentThemeProvider = $this->createMock(CurrentThemeProvider::class);
        $this->themeTransferJobMiddleware = new ThemeTransferJobMiddleware($this->currentThemeProvider);
    }

    public function testHandleWithExistingThemeId(): void
    {
        $message = new Message();
        $message->setProperty(ThemeTransferJobMiddleware::QUEUE_MESSAGE_THEME_ID, 'testTheme');

        $this->currentThemeProvider->expects($this->never())->method('getCurrentThemeId');

        $this->themeTransferJobMiddleware->handle($message);
    }

    public function testHandleWithoutRequestContext(): void
    {
        $message = new Message();

        $this->currentThemeProvider->method('getCurrentThemeId')->willThrowException(
            new NotRequestContextRuntimeException()
        );

        $this->themeTransferJobMiddleware->handle($message);

        $this->assertNull($message->getProperty(ThemeTransferJobMiddleware::QUEUE_MESSAGE_THEME_ID));
    }

    public function testHandleWithThemeId(): void
    {
        $message = new Message();

        $this->currentThemeProvider->method('getCurrentThemeId')->willReturn('testTheme');

        $this->themeTransferJobMiddleware->handle($message);

        $this->assertEquals('testTheme', $message->getProperty(ThemeTransferJobMiddleware::QUEUE_MESSAGE_THEME_ID));
    }
}
