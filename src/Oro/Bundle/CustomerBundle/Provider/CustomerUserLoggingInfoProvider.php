<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Provider\UserLoggingInfoProvider;

/**
 * Provides basic customer user info for logging purposes.
 */
class CustomerUserLoggingInfoProvider extends UserLoggingInfoProvider
{
    /**
     * @param CustomerUser|string $user
     * @return array
     */
    public function getUserLoggingInfo($user): array
    {
        $info = [];
        if ($user instanceof CustomerUser) {
            $info['customer_user'] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'fullname' => $user->getFullName(),
                'enabled' => $user->isEnabled(),
                'confirmed' => $user->isConfirmed(),
                'lastlogin' => $user->getLastLogin(),
                'createdat' => $user->getCreatedAt()
            ];
        } elseif (\is_string($user)) {
            $info['customer_user_email'] = $user;
        }

        $ip = $this->getIp();
        if ($ip) {
            $info['ipaddress'] = $ip;
        }

        return $info;
    }
}
