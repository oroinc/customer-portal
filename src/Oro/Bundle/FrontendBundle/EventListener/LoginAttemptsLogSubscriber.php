<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UserBundle\EventListener\LoginAttemptsSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Decorator that executes decorated LoginAttemptsSubscriberInterface implementation subscriber
 * only for not frontend requests.
 */
class LoginAttemptsLogSubscriber implements LoginAttemptsSubscriberInterface
{
    /** @var LoginAttemptsSubscriberInterface */
    private $innerSubscriber;

    /** @var FrontendHelper */
    private $frontendHelper;

    /**
     * @param LoginAttemptsSubscriberInterface $innerSubscriber
     * @param FrontendHelper                   $frontendHelper
     */
    public function __construct(LoginAttemptsSubscriberInterface $innerSubscriber, FrontendHelper $frontendHelper)
    {
        $this->innerSubscriber = $innerSubscriber;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
            SecurityEvents::INTERACTIVE_LOGIN            => 'onInteractiveLogin',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return;
        }

        $this->innerSubscriber->onAuthenticationFailure($event);
    }

    /**
     * {@inheritDoc}
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return;
        }

        $this->innerSubscriber->onInteractiveLogin($event);
    }
}
