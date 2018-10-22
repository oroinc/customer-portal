<?php

namespace Oro\Bundle\CustomerBundle\Security\Firewall;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * This listener authenticates anonymous (AKA guest) customer users at frontend
 */
class AnonymousCustomerUserAuthenticationListener implements ListenerInterface
{
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
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     * @param LoggerInterface|null $logger
     * @param ConfigManager $configManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        LoggerInterface $logger,
        ConfigManager $configManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->logger = $logger;
        $this->configManager = $configManager;
    }

    /**
     * @param WebsiteManager $websiteManager
     */
    public function setWebsiteManager(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
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
                $this->getRoles()
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
    protected function getRoles()
    {
        $currentWebsite = $this->websiteManager->getCurrentWebsite();
        if (!$currentWebsite) {
            return [];
        }
        /** @var CustomerUserRole $guestRole */
        $guestRole = $currentWebsite->getGuestRole();
        return [$guestRole->getRole()];
    }

    /**
     * @param Request $request
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
     * @param Request $request
     * @param AnonymousCustomerUserToken $token
     */
    private function saveCredentials(Request $request, AnonymousCustomerUserToken $token)
    {
        $visitor = $token->getVisitor();

        $cookieLifetime = $this->configManager->get('oro_customer.customer_visitor_cookie_lifetime_days');

        $cookieLifetime = $cookieLifetime * Configuration::SECONDS_IN_DAY;

        $request->attributes->set(
            self::COOKIE_ATTR_NAME,
            new Cookie(
                self::COOKIE_NAME,
                base64_encode(json_encode([$visitor->getId(), $visitor->getSessionId()])),
                time() + $cookieLifetime
            )
        );
    }
}
