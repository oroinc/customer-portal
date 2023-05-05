<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\UserBundle\Exception\CredentialsResetException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Adds check on customer user password status field.
 */
class CustomerUserChecker implements UserCheckerInterface
{
    private UserCheckerInterface $userCheckerInner;

    public function __construct(UserCheckerInterface $userCheckerInner)
    {
        $this->userCheckerInner = $userCheckerInner;
    }

    /**
     * {@inheritDoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        if ($user instanceof CustomerUser
            && $user->getAuthStatus()
            && $user->getAuthStatus()->getId() !== CustomerUserManager::STATUS_ACTIVE
        ) {
            $exception = new CredentialsResetException('Password reset.');
            $exception->setUser($user);

            throw $exception;
        }

        $this->userCheckerInner->checkPreAuth($user);
    }

    /**
     * {@inheritDoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        $this->userCheckerInner->checkPostAuth($user);
    }
}
