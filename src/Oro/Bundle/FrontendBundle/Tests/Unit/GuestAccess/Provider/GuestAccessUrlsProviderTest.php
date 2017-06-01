<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessUrlsProvider;

class GuestAccessUrlsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GuestAccessUrlsProvider
     */
    private $guestAccessUrlsProvider;

    protected function setUp()
    {
        $this->guestAccessUrlsProvider = new GuestAccessUrlsProvider();
    }

    public function testGetAllowedUrls()
    {
        $allowedUrls = [
            '^/exception/',
            '^/customer/user/login',
            '^/customer/user/reset-request',
            '^/customer/user/send-email',
            '^/customer/user/check-email',
            '^/customer/user/registration',
            '^/customer/user/confirm-email',
            '^/customer/user/reset',
        ];

        static::assertSame($allowedUrls, $this->guestAccessUrlsProvider->getAllowedUrls());
    }

    public function testGetRedirectUrls()
    {
        $redirectUrls = ['^/$'];

        static::assertSame($redirectUrls, $this->guestAccessUrlsProvider->getRedirectUrls());
    }
}
