<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Exception\EmptyCustomerException;
use Oro\Bundle\CustomerBundle\Exception\GuestCustomerUserLoginException;
use Oro\Bundle\UserBundle\Exception\EmptyOwnerException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Checks CustomerUser before auth
 */
class UserChecker implements UserCheckerInterface
{
    /** @var UserCheckerInterface */
    private $userCheckerInner;

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
