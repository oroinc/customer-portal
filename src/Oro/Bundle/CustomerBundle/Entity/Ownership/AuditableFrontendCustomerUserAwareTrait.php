<?php

namespace Oro\Bundle\CustomerBundle\Entity\Ownership;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;

/**
* AuditableFrontendCustomerUserAware trait
*
*/
trait AuditableFrontendCustomerUserAwareTrait
{
    use AuditableFrontendCustomerAwareTrait;

    #[ORM\ManyToOne(targetEntity: CustomerUser::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
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
