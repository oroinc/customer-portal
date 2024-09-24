<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UserBundle\Security\LoginAttemptsHandlerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Delegates handling of success and failed login to a specified storefront or backend handler.
 */
class CustomerUserLoginAttemptsLogListener implements ServiceSubscriberInterface
{
    private ContainerInterface $container;
    private FrontendHelper $frontendHelper;

    public function __construct(ContainerInterface $container, FrontendHelper $frontendHelper)
    {
        $this->container = $container;
        $this->frontendHelper = $frontendHelper;
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            'handler'          => LoginAttemptsHandlerInterface::class,
            'frontend_handler' => LoginAttemptsHandlerInterface::class
        ];
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $this->getHandler()->onInteractiveLogin($event);
    }

    public function onAuthenticationFailure(LoginFailureEvent $event): void
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
