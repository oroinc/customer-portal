<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Provider\UserLoggingInfoProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides basic customer user info for logging purposes.
 */
class CustomerUserLoggingInfoProvider implements UserLoggingInfoProviderInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getUserLoggingInfo(mixed $user): array
    {
        $info = [];
        if ($user instanceof CustomerUser) {
            $info['user'] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'fullname' => $user->getFullName(),
                'enabled' => $user->isEnabled(),
                'confirmed' => $user->isConfirmed(),
                'lastlogin' => $user->getLastLogin(),
                'createdat' => $user->getCreatedAt()
            ];
        } elseif (\is_string($user)) {
            $info['username'] = $user;
        }

        $ip = $this->getIp();
        if ($ip) {
            $info['ipaddress'] = $ip;
        }

        return $info;
    }

    private function getIp(): ?string
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return null;
        }

        return $this->requestStack->getCurrentRequest()->getClientIp();
    }
}
