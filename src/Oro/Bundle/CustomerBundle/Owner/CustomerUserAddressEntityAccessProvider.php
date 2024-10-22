<?php

namespace Oro\Bundle\CustomerBundle\Owner;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\SecurityBundle\Owner\OwnerChecker;
use Oro\Bundle\UserBundle\Entity\UserInterface;

/**
 * This class is used to check if customer user has permission
 * to create customer user address according to his role
 */
class CustomerUserAddressEntityAccessProvider
{
    public function __construct(
        private OwnerChecker $ownerChecker
    ) {
    }

    public function getCustomerUserAddressIfAllowed(UserInterface $customerUser): ?CustomerUserAddress
    {
        $customerUserAddress = (new CustomerUserAddress())
            ->setFrontendOwner($customerUser)
            ->setSystemOrganization($customerUser->getOrganization());

        if (!$this->ownerChecker->isOwnerCanBeSet($customerUserAddress)) {
            return null;
        }

        $customerUser->addAddress($customerUserAddress);

        return $customerUserAddress;
    }
}
