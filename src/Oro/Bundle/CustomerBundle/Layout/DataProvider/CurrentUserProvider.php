<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provides information about a current logged in used.
 */
class CurrentUserProvider
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getCurrentUser(): ?AbstractUser
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        if (!$user instanceof AbstractUser) {
            return null;
        }

        return $user;
    }

    public function isFrontendRequest(): bool
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token || !$token->isAuthenticated()) {
            return false;
        }

        return
            $token instanceof AnonymousCustomerUserToken
            || $token->getUser() instanceof CustomerUserInterface;
    }
}
