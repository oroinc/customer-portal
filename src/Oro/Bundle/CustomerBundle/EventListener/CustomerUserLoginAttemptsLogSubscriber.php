<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UserBundle\EventListener\LoginAttemptsHandlerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Delegates handling of success and failed login to a specified storefront or backend handler.
 */
class CustomerUserLoginAttemptsLogSubscriber implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private ContainerInterface $container;
    private FrontendHelper $frontendHelper;

    public function __construct(ContainerInterface $container, FrontendHelper $frontendHelper)
    {
        $this->container = $container;
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
    public static function getSubscribedServices(): array
    {
        return [
            'handler'          => LoginAttemptsHandlerInterface::class,
            'frontend_handler' => LoginAttemptsHandlerInterface::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $this->getHandler()->onInteractiveLogin($event);
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $this->getHandler()->onAuthenticationFailure($event);
    }

    private function getHandler(): LoginAttemptsHandlerInterface
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return $this->container->get('frontend_handler');
        }

        return $this->container->get('handler');
    }
}
