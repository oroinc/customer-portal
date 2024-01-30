<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticator;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

class CustomerVisitorContext extends OroFeatureContext
{
    /**
     * Example: Then Customer visitor cookie expiration date should be "+1 day"
     *
     * @Then /^Customer visitor cookie expiration date should be "(?P<value>(?:[^"]|\\")*)"$/
     *
     * @param string $value
     */
    public function assertCustomerVisitorCookieExpirationDate($value)
    {
        $cookie = $this->getSession()->getCookie(AnonymousCustomerUserAuthenticator::COOKIE_NAME);
        static::assertNotNull($cookie, 'Cannot find cookie');
        $cookies = $this->getSession()->getDriver()->getWebDriverSession()->getAllCookies();
        foreach ($cookies as $cookie) {
            if (AnonymousCustomerUserAuthenticator::COOKIE_NAME === $cookie['name']) {
                static::assertEquals(
                    date("d/m/Y", strtotime($value)),
                    date("d/m/Y", $cookie['expiry'])
                );
            }
        }
    }
}
