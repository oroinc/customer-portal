<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MessageQueueBundle\EventListener\LoginListener;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Decorates LoginListener from MessageQueueBundle to allow for FrontendHelper usage
 * which is not available in platform and other applications without customer-portal
 */
class FrontendLoginListenerDecorator
{
    /** @var KernelInterface */
    private $kernel;

    /** @var LoginListener */
    private $loginListener;

    /** @var FrontendHelper */
    private $frontendHelper;

    /**
     * @param KernelInterface $kernel
     * @param LoginListener $loginListener
     * @param FrontendHelper $frontendHelper
     */
    public function __construct(
        KernelInterface $kernel,
        LoginListener $loginListener,
        FrontendHelper $frontendHelper
    ) {
        $this->kernel = $kernel;
        $this->loginListener = $loginListener;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * Does nothing in case of frontend request to disable message queue consumer error message for store front users
     *
     * @param InteractiveLoginEvent $event
     */
    public function onLogin(InteractiveLoginEvent $event)
    {
        if ($this->frontendHelper->isFrontendRequest($event->getRequest())
            && $this->kernel->getEnvironment() === 'prod'
        ) {
            return;
        }

        $this->loginListener->onLogin($event);
    }
}
