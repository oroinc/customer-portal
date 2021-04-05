<?php

namespace Oro\Bundle\CustomerBundle\Security\Firewall;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Factory that creates the customer visitor cookie.
 */
class CustomerVisitorCookieFactory
{
    /** @var mixed true, false, 'auto' */
    private $secure;

    /** @var bool */
    private $httpOnly;

    /** @var string|null */
    private $sameSite;

    /** @var ConfigManager */
    private $configManager;

    /**
     * @param mixed         $secure
     * @param bool          $httpOnly
     * @param ConfigManager $configManager
     * @param string|null   $sameSite
     */
    public function __construct($secure, bool $httpOnly, ConfigManager $configManager, ?string $sameSite)
    {
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
        $this->configManager = $configManager;
    }

    /**
     * @param int $visitorId
     * @param int $visitorSessionId
     * @param int $lifeTime
     *
     * @return Cookie
     */
    public function getCookie($visitorId, $visitorSessionId, $lifeTime = Configuration::SECONDS_IN_DAY): Cookie
    {
        $cookieLifetime = $this->configManager->get('oro_customer.customer_visitor_cookie_lifetime_days');
        $cookieLifetime *= $lifeTime;

        return new Cookie(
            AnonymousCustomerUserAuthenticationListener::COOKIE_NAME,
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
