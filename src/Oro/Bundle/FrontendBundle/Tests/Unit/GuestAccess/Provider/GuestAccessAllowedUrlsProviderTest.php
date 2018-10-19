<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProvider;

class GuestAccessAllowedUrlsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GuestAccessAllowedUrlsProvider
     */
    private $guestAccessAllowedUrlsProvider;

    protected function setUp()
    {
        $this->guestAccessAllowedUrlsProvider = new GuestAccessAllowedUrlsProvider();
    }

    public function testGetAllowedUrlsPatterns()
    {
        $allowedUrls = [
            '^/exception/',
            '^/_profiler',
            '^/_wdt',
            '^/_fragment',
            '^/js/',
            '^/embedded-form',
            '^/customer/user/login$',
            '^/customer/user/reset-request$',
            '^/customer/user/send-email$',
            '^/customer/user/check-email$',
            '^/customer/user/registration$',
            '^/customer/user/confirm-email$',
            '^/customer/user/reset$',
            '^/api/',
        ];
        $this->guestAccessAllowedUrlsProvider->addAllowedUrlPattern('^/api/');

        static::assertSame($allowedUrls, $this->guestAccessAllowedUrlsProvider->getAllowedUrlsPatterns());
    }
}
