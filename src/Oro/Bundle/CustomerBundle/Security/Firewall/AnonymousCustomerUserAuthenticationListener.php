<?php

namespace Oro\Bundle\CustomerBundle\Security\Firewall;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;

class AnonymousCustomerUserAuthenticationListener implements ListenerInterface
{
    const ANONYMOUS_CUSTOMER_USER_ROLE = 'ROLE_FRONTEND_ANONYMOUS';

    const COOKIE_ATTR_NAME = '_security_customer_visitor_cookie';
    const COOKIE_NAME = 'customer_visitor';

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var integer
     */
    private $cookieLifetime;

    /**
     * @param TokenStorageInterface          $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     * @param LoggerInterface|null           $logger
     * @param integer                        $cookieLifetime
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        LoggerInterface $logger,
        $cookieLifetime
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->logger = $logger;
        $this->cookieLifetime = $cookieLifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token || $token instanceof AnonymousCustomerUserToken) {
            $request = $event->getRequest();

            $token = new AnonymousCustomerUserToken(
                'Anonymous Customer User',
                [self::ANONYMOUS_CUSTOMER_USER_ROLE]
            );
            $token->setCredentials($this->getCredentials($request));

            try {
                $newToken = $this->authenticationManager->authenticate($token);

                $this->tokenStorage->setToken($newToken);
                $this->saveCredentials($request, $newToken);

                $this->logger->info('Populated the TokenStorage with an Anonymous Customer User Token.');
            } catch (AuthenticationException $e) {
                $this->logger->info('Customer User anonymous authentication failed.', ['exception' => $e]);
            }
        }
    }

    /**
     * @return array
     */
    private function getCredentials(Request $request)
    {
        $value = $request->cookies->get(self::COOKIE_NAME);
        if ($value) {
            list($visitorId, $sessionId) = json_decode(base64_decode($value));
        } else {
            $visitorId = null;
            $sessionId = null;
        }

        return [
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
        ];
    }

    /**
     * @param array   $credentials
     * @param integer $lifetime
     */
    private function saveCredentials(Request $request, AnonymousCustomerUserToken $token)
    {
        $visitor = $token->getVisitor();

        $request->attributes->set(
            self::COOKIE_ATTR_NAME,
            new Cookie(
                self::COOKIE_NAME,
                base64_encode(json_encode([$visitor->getId(), $visitor->getSessionId()])),
                time() + $this->cookieLifetime
            )
        );
    }
}
