<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Entity\WebsiteAwareInterface;

/**
 * Interface for CustomerUser
 */
interface CustomerUserInterface extends
    FullNameInterface,
    WebsiteAwareInterface,
    CustomerUserIdentity
{
    /**
     * @return User|null
     */
    public function getOwner(): ?User;

    /**
     * @param User $owner
     * @return CustomerUserInterface
     */
    public function setOwner(User $owner);
}
