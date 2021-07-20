<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MessageQueueBundle\EventListener\LoginListener;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * The decorator for {@see \Oro\Bundle\MessageQueueBundle\EventListener\LoginListener}
 * to disable the message queue consumer alive message for storefront users.
 */
class FrontendLoginListenerDecorator
{
    /** @var KernelInterface */
    private $kernel;

    /** @var LoginListener */
    private $loginListener;

    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(
        KernelInterface $kernel,
        LoginListener $loginListener,
        FrontendHelper $frontendHelper
    ) {
        $this->kernel = $kernel;
        $this->loginListener = $loginListener;
        $this->frontendHelper = $frontendHelper;
    }

    public function onLogin(InteractiveLoginEvent $event)
    {
        if ($this->frontendHelper->isFrontendUrl($event->getRequest()->getPathInfo())
            && $this->kernel->getEnvironment() === 'prod'
        ) {
            return;
        }

        $this->loginListener->onLogin($event);
    }
}
