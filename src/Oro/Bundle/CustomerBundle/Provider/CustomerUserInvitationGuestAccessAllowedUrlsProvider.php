<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface;

/**
 * Allows invited users to open a valid invitation URL when storefront guest access is disabled.
 */
class CustomerUserInvitationGuestAccessAllowedUrlsProvider implements GuestAccessAllowedUrlsProviderInterface
{
    #[\Override]
    public function getAllowedUrlsPatterns(): array
    {
        return ['^/customer/user/invitations/accept/[a-f0-9]{64}$'];
    }
}
