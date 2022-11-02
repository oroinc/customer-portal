<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\NavigationBundle\Event\ResponseHashnavListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;

/**
 * Sets parameters to request the that determine the layout structure
 */
class ThemeListener
{
    private FrontendHelper $frontendHelper;

    private ConfigManager $configManager;

    public function __construct(FrontendHelper $frontendHelper, ConfigManager $configManager)
    {
        $this->frontendHelper = $frontendHelper;
        $this->configManager = $configManager;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($this->frontendHelper->isFrontendUrl($request->getPathInfo())) {
            // set layout theme
            $layoutTheme = $this->configManager->get('oro_frontend.frontend_theme');
            $request->attributes->set('_theme', $layoutTheme);

            // disable SPA
            $hashNavigationHeader =
                $request->get(ResponseHashnavListener::HASH_NAVIGATION_HEADER)
                || $request->headers->get(ResponseHashnavListener::HASH_NAVIGATION_HEADER);
            if ($hashNavigationHeader && !$request->attributes->has('_fullRedirect')) {
                $request->attributes->set('_fullRedirect', true);
            }
        }
    }

    public function onKernelView(ViewEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->frontendHelper->isFrontendUrl($request->getPathInfo())) {
            return;
        }

        if ($request->attributes->has('_layout')) {
            $request->attributes->remove('_template');
        } elseif ($request->attributes->has('_template')) {
            $request->attributes->remove('_layout');
        }
    }
}
