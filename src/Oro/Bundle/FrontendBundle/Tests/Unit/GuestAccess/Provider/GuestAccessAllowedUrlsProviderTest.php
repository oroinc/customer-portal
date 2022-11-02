<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\GuestAccess\Provider;

use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProvider;

class GuestAccessAllowedUrlsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GuestAccessAllowedUrlsProvider
     */
    private $guestAccessAllowedUrlsProvider;

    protected function setUp(): void
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
            '^/media/js/',
            '^/media/cache/',
            '^/embedded-form',
            '^/customer/user/login$',
            '^/customer/user/reset-request$',
            '^/customer/user/send-email$',
            '^/customer/user/check-email$',
            '^/customer/user/registration$',
            '^/customer/user/confirm-email$',
            '^/customer/user/reset$',
            '^/localization/set-current-localization$',
            '^/productprice/set-current-currency$',
            '^/cookies-accepted$',
            '^/api/',
        ];
        $this->guestAccessAllowedUrlsProvider->addAllowedUrlPattern('^/api/');

        self::assertSame($allowedUrls, $this->guestAccessAllowedUrlsProvider->getAllowedUrlsPatterns());
    }
}
