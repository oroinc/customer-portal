<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Sets the request to theme-manager, as it is called during kernel.terminate event,
 * moment when the request context is already emptied
 */
class ThemeManagerRequestSetterListener
{
    public function __construct(
        private readonly CurrentThemeProvider $currentThemeProvider
    ) {
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $this->currentThemeProvider->setCurrentRequest($event->getRequest());
    }
}
