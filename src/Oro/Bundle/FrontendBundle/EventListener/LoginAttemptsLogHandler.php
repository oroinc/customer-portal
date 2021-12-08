<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UserBundle\EventListener\LoginAttemptsHandlerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Decorator that executes decorated LoginAttemptsHandlerInterface implementation
 * only for not frontend requests.
 * @deprecated this handler is not used and will be removed in v5.0
 */
class LoginAttemptsLogHandler implements LoginAttemptsHandlerInterface
{
    /** @var LoginAttemptsHandlerInterface */
    private $innerHandler;

    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(LoginAttemptsHandlerInterface $innerHandler, FrontendHelper $frontendHelper)
    {
        $this->innerHandler = $innerHandler;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $this->innerHandler->onAuthenticationFailure($event);
    }

    /**
     * {@inheritDoc}
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->innerHandler->onInteractiveLogin($event);
    }
}
