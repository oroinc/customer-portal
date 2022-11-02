<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Exception\EmptyCustomerException;
use Oro\Bundle\CustomerBundle\Exception\GuestCustomerUserLoginException;
use Oro\Bundle\UserBundle\Exception\EmptyOwnerException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Checks the state of CustomerUser during authentication.
 */
class UserChecker implements UserCheckerInterface
{
    private UserCheckerInterface $userCheckerInner;

    public function __construct(UserCheckerInterface $userCheckerInner)
    {
        $this->userCheckerInner = $userCheckerInner;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        $this->userCheckerInner->checkPostAuth($user);

        if ($user instanceof CustomerUser) {
            if (!$user->isEnabled()) {
                $exception = new DisabledException('The customer user is disabled.');
                $exception->setUser($user);

                throw $exception;
            }
            if (!$user->isConfirmed()) {
                $exception = new LockedException('The customer user is not confirmed.');
                $exception->setUser($user);

                throw $exception;
            }
            if (!$user->getCustomer()) {
                $exception = new EmptyCustomerException('The customer user does not have a customer.');
                $exception->setUser($user);

                throw $exception;
            }
            if (!$user->getOwner()) {
                $exception = new EmptyOwnerException('The customer user does not have an owner.');
                $exception->setUser($user);

                throw $exception;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        if ($user instanceof CustomerUser && $user->isGuest()) {
            $exception = new GuestCustomerUserLoginException('The customer user is a guest.');
            $exception->setUser($user);

            throw $exception;
        }

        $this->userCheckerInner->checkPreAuth($user);
    }
}
