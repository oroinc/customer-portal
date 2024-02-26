<?php

namespace Oro\Bundle\CustomerBundle\Entity\Ownership;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
* FrontendCustomerUserAware trait
*
*/
trait FrontendCustomerUserAwareTrait
{
    use FrontendCustomerAwareTrait;

    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?CustomerUser $customerUser = null;

    /**
     * @return CustomerUser|null
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @param CustomerUser|null $customerUser
     * @return $this
     */
    public function setCustomerUser(CustomerUser $customerUser = null)
    {
        $this->customerUser = $customerUser;

        if ($customerUser && $customerUser->getCustomer()) {
            $this->setCustomer($customerUser->getCustomer());
        }

        return $this;
    }
}
