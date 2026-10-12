<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Provider\CustomerUserInvitationGuestAccessAllowedUrlsProvider;
use PHPUnit\Framework\TestCase;

class CustomerUserInvitationGuestAccessAllowedUrlsProviderTest extends TestCase
{
    public function testGetAllowedUrlsPatterns(): void
    {
        $provider = new CustomerUserInvitationGuestAccessAllowedUrlsProvider();

        self::assertEquals(
            ['^/customer/user/invitations/accept/[a-f0-9]{64}$'],
            $provider->getAllowedUrlsPatterns()
        );
    }
}
