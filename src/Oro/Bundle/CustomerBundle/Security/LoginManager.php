<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationTokenFactoryInterface;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

/**
 * Logins CustomerUser if authentication checks passed
 */
class LoginManager
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @var SessionAuthenticationStrategyInterface
     */
    private $sessionStrategy;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var RememberMeServicesInterface
     */
    private $rememberMeService;

    /**
     * @var UsernamePasswordOrganizationTokenFactoryInterface
     */
    private $tokenFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * LoginManager constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $userChecker,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        RequestStack $requestStack,
        UsernamePasswordOrganizationTokenFactoryInterface $tokenFactory,
        EventDispatcherInterface $eventDispatcher,
        RememberMeServicesInterface $rememberMeService = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userChecker = $userChecker;
        $this->sessionStrategy = $sessionStrategy;
        $this->requestStack = $requestStack;
        $this->tokenFactory = $tokenFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->rememberMeService = $rememberMeService;
    }

    public function logInUser($firewallName, AbstractUser $user, Response $response = null)
    {
        try {
            $this->userChecker->checkPreAuth($user);

            $token = $this->createToken($firewallName, $user);
            $request = $this->requestStack->getCurrentRequest();

            if (null !== $request) {
                $this->sessionStrategy->onAuthentication($request, $token);

                if (null !== $response && null !== $this->rememberMeService) {
                    $this->rememberMeService->loginSuccess($request, $response, $token);
                }
            }

            $this->tokenStorage->setToken($token);

            $event = new InteractiveLoginEvent($request, $token);
            $this->eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);
        } catch (AccountStatusException $exception) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }
    }

    /**
     * @param string $firewall
     * @param AbstractUser $user
     * @return UsernamePasswordToken
     */
    private function createToken($firewall, AbstractUser $user)
    {
        return $this->tokenFactory->create($user, null, $firewall, $user->getOrganization(), $user->getUserRoles());
    }
}
