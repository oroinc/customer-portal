<?php

namespace Oro\Bundle\CustomerBundle\Security\Firewall;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticator;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Factory that creates the customer visitor cookie.
 */
class CustomerVisitorCookieFactory
{
    /**
     * @param string|bool $secure [true, false, 'auto']
     */
    public function __construct(
        private string|bool $secure,
        private bool $httpOnly,
        private ConfigManager $configManager,
        private ?string $sameSite
    ) {
    }

    public function getCookie(
        string $visitorSessionId,
        ?int $visitorId,
        int $lifeTime = Configuration::SECONDS_IN_DAY
    ): Cookie {
        $cookieLifetime = $this->configManager->get('oro_customer.customer_visitor_cookie_lifetime_days');
        $cookieLifetime *= $lifeTime;

        return new Cookie(
            AnonymousCustomerUserAuthenticator::COOKIE_NAME,
            base64_encode(json_encode([$visitorId, $visitorSessionId])),
            time() + $cookieLifetime,
            '/',
            null,
            'auto' === $this->secure ? null : $this->secure,
            $this->httpOnly,
            false,
            $this->sameSite
        );
    }
}
