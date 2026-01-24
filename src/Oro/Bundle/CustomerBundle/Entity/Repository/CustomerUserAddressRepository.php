<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Repository for customer user addresses with support for typed addresses and ACL filtering.
 */
class CustomerUserAddressRepository extends AbstractDefaultTypedAddressRepository
{
    /**
     * @param CustomerUser $customerUser
     * @param string $type
     * @param AclHelper $aclHelper
     * @return array
     */
    public function getAddressesByType(CustomerUser $customerUser, $type, AclHelper $aclHelper)
    {
        $query = $aclHelper->apply($this->getAddressesByTypeQueryBuilder($customerUser, $type));

        return $query->getResult();
    }

    /**
     * @param CustomerUser $customerUser
     * @param string $type
     * @param AclHelper $aclHelper
     * @return array
     */
    public function getDefaultAddressesByType(CustomerUser $customerUser, $type, AclHelper $aclHelper)
    {
        $query = $aclHelper->apply($this->getDefaultAddressesQueryBuilder($customerUser, $type));

        return $query->getResult();
    }
}
