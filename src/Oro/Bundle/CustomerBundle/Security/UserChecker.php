<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Exception\GuestCustomerUserLoginException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Checks CustomerUser before auth
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * @var UserCheckerInterface
     */
    private $userCheckerInner;

    /**
     * @param UserCheckerInterface $userCheckerInner
     */
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
    }

    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        if ($user instanceof CustomerUser && $user->isGuest()) {
            throw new GuestCustomerUserLoginException('Customer User is Guest.');
        }

        $this->userCheckerInner->checkPreAuth($user);
    }
}
