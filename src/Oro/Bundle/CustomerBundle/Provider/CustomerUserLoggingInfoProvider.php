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

        return array_merge($info, $this->getClientInfo());
    }

    private function getClientInfo(): array
    {
        $result = [];

        if (!$this->requestStack->getCurrentRequest()) {
            return $result;
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request->server->has('HTTP_SEC_CH_UA_PLATFORM')) {
            $result['platform'] = trim($request->server->get('HTTP_SEC_CH_UA_PLATFORM'), '"');
        }
        if ($request->server->has('HTTP_USER_AGENT')) {
            $result['user agent'] = $request->server->get('HTTP_USER_AGENT');
        }

        return $result;
    }

    private function getIp(): ?string
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return null;
        }

        return $this->requestStack->getCurrentRequest()->getClientIp();
    }
}
