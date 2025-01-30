<?php

namespace Oro\Bundle\CustomerBundle\Entity\Ownership;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;

/**
* AuditableFrontendCustomerAware trait
*
*/
trait AuditableFrontendCustomerAwareTrait
{
    #[ORM\ManyToOne(targetEntity: Customer::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?Customer $customer = null;

    /**
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     * @return $this
     */
    public function setCustomer(?Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }
}
