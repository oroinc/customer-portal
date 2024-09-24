<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\FrontendBundle\EventListener\ThemeManagerRequestSetterListener;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ThemeManagerRequestSetterListenerTest extends TestCase
{
    private CurrentThemeProvider $currentThemeProvider;
    private ThemeManagerRequestSetterListener $themeManagerRequestSetterListener;

    #[\Override]
    protected function setUp(): void
    {
        $this->currentThemeProvider = $this->createMock(CurrentThemeProvider::class);
        $this->themeManagerRequestSetterListener = new ThemeManagerRequestSetterListener($this->currentThemeProvider);
    }

    public function testOnKernelTerminateWithRequest(): void
    {
        $request = new Request();
        $event = new TerminateEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $this->createMock(Response::class)
        );

        $this->currentThemeProvider->expects($this->once())->method('setCurrentRequest')->with($request);

        $this->themeManagerRequestSetterListener->onKernelTerminate($event);
    }
}
