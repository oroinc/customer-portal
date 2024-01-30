<?php

namespace Oro\Bundle\CustomerBundle\Security\Passport;

use Oro\Bundle\CustomerBundle\Security\Badge\AnonymousCustomerUserBadge;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Anonymous self validation passport.
 */
class AnonymousSelfValidatingPassport extends SelfValidatingPassport
{
    public function getUser(): UserInterface
    {
        if (null === $this->user) {
            if (!$this->hasBadge(AnonymousCustomerUserBadge::class)) {
                throw new \LogicException(
                    'Cannot get the Security user, no username or UserBadge configured for this passport.'
                );
            }

            $this->user = $this->getBadge(AnonymousCustomerUserBadge::class)->getUser();
        }

        return $this->user;
    }
}
